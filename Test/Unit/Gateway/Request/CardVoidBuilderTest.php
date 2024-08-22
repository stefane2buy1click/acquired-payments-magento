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
use Acquired\Payments\Gateway\Request\CardVoidBuilder;
use Acquired\Payments\Exception\Command\BuilderException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

class CardVoidBuilderTest extends TestCase
{
    private $loggerMock;
    private $cardVoidBuilder;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->cardVoidBuilder = new CardVoidBuilder($this->loggerMock);
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
        $buildSubject = [
            'payment' => $this->getPaymentMock(100.00, '10001'),
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
            'payment' => $this->getPaymentMock(0.00, null)
        ];

        $this->cardVoidBuilder->build($buildSubject);
    }

}
