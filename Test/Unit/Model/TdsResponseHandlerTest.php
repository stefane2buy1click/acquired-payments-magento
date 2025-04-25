<?php

declare(strict_types=1);

/**
 * Acquired.com Payments Integration for Magento2
 *
 * Copyright (c) 2024 Acquired Limited (https://acquired.com/)
 *
 * This file is open source under the MIT license.
 * Please see LICENSE file for more details.
 */

namespace Acquired\Payments\Test\Unit\Model;

use Acquired\Payments\Model\TdsResponseHandler;
use Acquired\Payments\Gateway\Config\Basic;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Phrase;
use Acquired\Payments\Gateway\Validator\TransactionDataIntegrityValidator;

class TdsResponseHandlerTest extends TestCase
{
    private $basicConfigMock;
    private $loggerMock;
    private $transactionDataIntegrityValidatorMock;
    private $tdsResponseHandler;

    protected function setUp(): void
    {
        $this->basicConfigMock = $this->createMock(Basic::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->transactionDataIntegrityValidatorMock = $this->createMock(TransactionDataIntegrityValidator::class);
        $this->tdsResponseHandler = new TdsResponseHandler($this->basicConfigMock, $this->transactionDataIntegrityValidatorMock, $this->loggerMock);

        // Configure the basicConfigMock to return a predefined API secret for hash generation
        $this->basicConfigMock->method('getApiSecret')->willReturn('secret');
    }

    public function testProcessResponseWithValidData()
    {
        $postData = [
            'status' => 'success',
            'transaction_id' => '123',
            'order_id' => '456',
            'timestamp' => '789',
            'hash' => hash('sha256', hash('sha256', 'success123456789') . 'secret')
        ];

        $this->transactionDataIntegrityValidatorMock
            ->method('validateIntegrity')
            ->with($postData)
            ->willReturn(true);

        $response = $this->tdsResponseHandler->processResponse($postData);

        $this->assertEquals('TdsResponse', $response['type']);
        $this->assertEquals('success', $response['status']);
        $this->assertStringContainsString('3-D Secure verification completed', (string) $response['message']);
    }

    public function testProcessResponseWithInvalidData()
    {
        $postData = [
            'status' => 'success',
            'transaction_id' => '123',
            'order_id' => '456',
            'timestamp' => '789',
            'hash' => 'invalid_hash'
        ];

        $this->transactionDataIntegrityValidatorMock
            ->method('validateIntegrity')
            ->with($postData)
            ->willThrowException(new \Exception('Invalid 3-D Secure data integrity!'));

        $response = $this->tdsResponseHandler->processResponse($postData);

        $this->assertEquals('TdsResponse', $response['type']);
        $this->assertEquals('error', $response['status']);
        $this->assertStringContainsString('Invalid 3-D Secure data integrity!', (string) $response['message']);
    }

    public function testProcessResponseThrowsException()
    {
        $postData = ['invalid' => 'data'];

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($this->isInstanceOf(Phrase::class), $this->arrayHasKey('exception'));

        $this->transactionDataIntegrityValidatorMock
            ->method('validateIntegrity')
            ->willThrowException(new \Exception('An error occurred while processing the 3-D Secure response.'));

        $response = $this->tdsResponseHandler->processResponse($postData);

        $this->assertEquals('TdsResponse', $response['type']);
        $this->assertEquals('error', $response['status']);
        $this->assertStringContainsString('An error occurred while processing the 3-D Secure response.', (string) $response['message']);
    }
}
