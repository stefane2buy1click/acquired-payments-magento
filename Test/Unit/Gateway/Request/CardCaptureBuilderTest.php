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

use Acquired\Payments\Gateway\Request\CardCaptureBuilder;
use Acquired\Payments\Gateway\Config\Card\Config as CardConfig;
use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Acquired\Payments\Service\MultishippingService;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order;
use Acquired\Payments\Exception\Command\BuilderException;
use Acquired\Payments\Test\Unit\Gateway\Request\AbstractBuilderTestCase;

class CardCaptureBuilderTest extends AbstractBuilderTestCase
{
    private $cardCaptureBuilder;
    private $cardConfigMock;
    private $loggerMock;
    private $checkoutSessionMock;
    private $multishippingServiceMock;
    private $paymentDOInterfaceMock;
    private $paymentMock;
    private $orderMock;

    protected function setUp(): void
    {
        $this->cardConfigMock = $this->createMock(CardConfig::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->checkoutSessionMock = $this->createMock(CheckoutSession::class);
        $this->multishippingServiceMock = $this->createMock(MultishippingService::class);
        $this->paymentDOInterfaceMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->orderMock = $this->createMock(Order::class);


        $this->cardCaptureBuilder = new CardCaptureBuilder(
            $this->cardConfigMock,
            $this->loggerMock,
            $this->checkoutSessionMock,
            $this->multishippingServiceMock
        );
    }

    public function testBuildSuccess()
    {
        $transactionId = '12345';
        $expectedResult = [
            'transaction_id' => $transactionId,
            'amount' => ['amount' => 100.00],
            'is_captured' => false
        ];

        $paymentDataMock = $this->getPaymentMock(100.00, $transactionId, '100000001');

        $buildSubject = [
            'payment' => $paymentDataMock,
            'amount'  => 100.00
        ];

        $result = $this->cardCaptureBuilder->build($buildSubject);

        $this->assertEquals($expectedResult, $result);
    }

    public function testMissingAmountBuildException()
    {
        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('Amount should be provided');

        $paymentDataMock = $this->getPaymentMock(null, '123456', '100000001');
        $buildSubject = [
            'payment' => $paymentDataMock
        ];

        $this->loggerMock->expects($this->once())->method('critical');

        $this->cardCaptureBuilder->build($buildSubject);
    }

    public function testMissingTransactionBuildException()
    {
        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('Missing transaction_id');

        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->method('getOrder')->willReturn($this->orderMock);
        $paymentMock->method('getAdditionalInformation')->with('transaction_id')->willReturn(null);
        $paymentMock->method('getTransactionId')->willReturn('12345');

        $this->paymentDOInterfaceMock->method('getPayment')->willReturn($paymentMock);
        $this->paymentDOInterfaceMock->method('getOrder')->willReturn($this->orderMock);

        $buildSubject = [
            'payment' => $this->paymentDOInterfaceMock
        ];

        $this->loggerMock->expects($this->once())->method('critical');
        $this->cardCaptureBuilder->build($buildSubject);
    }

    public function testBuildExceptionHandling()
    {
        $this->expectException(BuilderException::class);

        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->method('getOrder')->willThrowException(new \Exception('Test Exception'));

        $buildSubject = ['payment' => $paymentMock];

        $this->loggerMock->expects($this->once())->method('critical');

        $this->cardCaptureBuilder->build($buildSubject);
    }
}
