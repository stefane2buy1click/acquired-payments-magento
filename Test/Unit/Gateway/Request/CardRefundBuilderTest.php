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

use Acquired\Payments\Gateway\Request\CardRefundBuilder;
use Psr\Log\LoggerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Acquired\Payments\Exception\Command\BuilderException;
use Acquired\Payments\Test\Unit\Gateway\Request\AbstractBuilderTestCase;

class CardRefundBuilderTest extends AbstractBuilderTestCase
{
    private $loggerMock;
    private $priceCurrencyMock;
    private $cardRefundBuilder;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);
        $this->cardRefundBuilder = new CardRefundBuilder(
            $this->loggerMock,
            $this->priceCurrencyMock
        );
    }

    public function testBuildSuccess()
    {
        $expectedResult = [
            'transaction_id' => '123',
            'grand_total' => 50.0,
            'reference' => [
                'reference' => '100000001',
                'amount' => 50.0
            ]
        ];

        $this->priceCurrencyMock->method('round')->willReturn(50.0);

        $buildSubject = [
            'payment' => $this->getPaymentMock(50.0, '123', '100000001'),
            'amount' => 50.0
        ];

        $result = $this->cardRefundBuilder->build($buildSubject);

        $this->assertEquals($expectedResult, $result);
    }

    public function testMissingAmountBuildException()
    {
        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('Amount should be provided');

        $buildSubject = [
            'payment' => $this->getPaymentMock(0, '123', '100000001'),
        ];

        $this->cardRefundBuilder->build($buildSubject);
    }


    public function testBuildThrowsExceptionForZeroAmount()
    {
        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('Refunds cannot be processed if the amount is 0. Please specify a different amount.');

        $buildSubject = [
            'payment' => $this->getPaymentMock(0, '123', '100000001'),
            'amount' => 0
        ];

        $this->cardRefundBuilder->build($buildSubject);
    }


    public function testBuildThrowsExceptionForMissingTransactionId()
    {
        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('Missing transaction_id');

        $buildSubject = [
            'payment' => $this->getPaymentMock(100, null, '100000001'),
            'amount' => 100
        ];

        $this->cardRefundBuilder->build($buildSubject);
    }


}
