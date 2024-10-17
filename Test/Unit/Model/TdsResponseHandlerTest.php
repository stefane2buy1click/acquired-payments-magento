<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Test\Unit\Model;

use Acquired\Payments\Model\TdsResponseHandler;
use Acquired\Payments\Gateway\Config\Basic;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Phrase;

class TdsResponseHandlerTest extends TestCase
{
    private $basicConfigMock;
    private $loggerMock;
    private $tdsResponseHandler;

    protected function setUp(): void
    {
        $this->basicConfigMock = $this->createMock(Basic::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->tdsResponseHandler = new TdsResponseHandler($this->basicConfigMock, $this->loggerMock);

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

        $response = $this->tdsResponseHandler->processResponse($postData);

        $this->assertEquals('TdsResponse', $response['type']);
        $this->assertEquals('error', $response['status']);
        $this->assertStringContainsString('An error occurred while processing the 3-D Secure response.', (string) $response['message']);
    }
}
