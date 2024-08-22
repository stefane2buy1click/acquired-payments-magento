<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Test\Unit\Gateway\Request;

use PHPUnit\Framework\TestCase;
use Acquired\Payments\Gateway\Request\CardRefundBuilder;
use Psr\Log\LoggerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order;
use Acquired\Payments\Exception\Command\BuilderException;

class CardRefundBuilderTest extends TestCase
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

    private function getPaymentMock(float $amount, $transactionId): PaymentDataObjectInterface
    {
        $orderMock = $this->createMock(Order::class);
        $orderMock->method('getGrandTotal')->willReturn($amount);
        $orderMock->method('getIncrementId')->willReturn('100000001');

        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->method('getOrder')->willReturn($orderMock);
        $paymentMock->method('getAdditionalInformation')->with('transaction_id')->willReturn($transactionId);
        $paymentMock->method('getLastTransId')->willReturn($transactionId);

        $paymentDataObjectMock = $this->createMock(PaymentDataObjectInterface::class);
        $paymentDataObjectMock->method('getPayment')->willReturn($paymentMock);

        return $paymentDataObjectMock;
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
            'payment' => $this->getPaymentMock(50.0, '123'),
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
            'payment' => $this->getPaymentMock(0, '123'),
        ];

        $this->cardRefundBuilder->build($buildSubject);
    }


    public function testBuildThrowsExceptionForZeroAmount()
    {
        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('Refunds cannot be processed if the amount is 0. Please specify a different amount.');

        $buildSubject = [
            'payment' => $this->getPaymentMock(0, '123'),
            'amount' => 0
        ];

        $this->cardRefundBuilder->build($buildSubject);
    }


    public function testBuildThrowsExceptionForMissingTransactionId()
    {
        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('Missing transaction_id');

        $buildSubject = [
            'payment' => $this->getPaymentMock(100, null),
            'amount' => 100
        ];

        $this->cardRefundBuilder->build($buildSubject);
    }


}
