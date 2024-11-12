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

namespace Acquired\Payments\Test\Unit\Gateway\Request;

use Acquired\Payments\Gateway\Request\CardVoidBuilder;
use Acquired\Payments\Exception\Command\BuilderException;
use Psr\Log\LoggerInterface;
use Acquired\Payments\Test\Unit\Gateway\Request\AbstractBuilderTestCase;

class CardVoidBuilderTest extends AbstractBuilderTestCase
{
    private $loggerMock;
    private $cardVoidBuilder;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->cardVoidBuilder = new CardVoidBuilder($this->loggerMock);
    }

    public function testBuildSuccess()
    {
        $buildSubject = [
            'payment' => $this->getPaymentMock(100.00, '10001', '100000001'),
        ];

        $result = $this->cardVoidBuilder->build($buildSubject);

        $this->assertArrayHasKey('transaction_id', $result);
        $this->assertEquals('10001', $result['transaction_id']);
        $this->assertArrayHasKey('reference', $result);
        $this->assertEquals(['reference' => '100000001'], $result['reference']);
    }

    public function testBuildMissingTransactionIdFailure()
    {
        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('Missing transaction_id');

        $buildSubject = [
            'payment' => $this->getPaymentMock(0.00, null, '100000001')
        ];

        $this->cardVoidBuilder->build($buildSubject);
    }

}
