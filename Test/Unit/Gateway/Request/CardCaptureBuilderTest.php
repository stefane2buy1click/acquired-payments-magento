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
use Acquired\Payments\Gateway\Request\CardCaptureBuilder;
use Acquired\Payments\Gateway\Config\Card\Config as CardConfig;
use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Acquired\Payments\Service\MultishippingService;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order;
use Acquired\Payments\Exception\Command\BuilderException;

class CardCaptureBuilderTest extends TestCase
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
        $expectedResult = [
            'transaction_id' => '12345',
            'amount' => ['amount' => 100.00],
            'is_captured' => false
        ];

        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->method('getOrder')->willReturn($this->orderMock);
        $paymentMock->method('getAdditionalInformation')->with('transaction_id')->willReturn('12345');
        $paymentMock->method('getTransactionId')->willReturn('12345');

        $this->paymentDOInterfaceMock->method('getPayment')->willReturn($paymentMock);
        $this->paymentDOInterfaceMock->method('getOrder')->willReturn($this->orderMock);


        $buildSubject = [
            'payment' => $this->paymentDOInterfaceMock,
            'amount'  => 100.00
        ];

        $result = $this->cardCaptureBuilder->build($buildSubject);

        $this->assertEquals($expectedResult, $result);
    }

    public function testMissingAmountBuildException()
    {
        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('Amount should be provided');

        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->method('getOrder')->willReturn($this->orderMock);
        $paymentMock->method('getAdditionalInformation')->with('transaction_id')->willReturn('123456');
        $paymentMock->method('getTransactionId')->willReturn('12345');

        $this->paymentDOInterfaceMock->method('getPayment')->willReturn($paymentMock);
        $this->paymentDOInterfaceMock->method('getOrder')->willReturn($this->orderMock);

        $buildSubject = [
            'payment' => $this->paymentDOInterfaceMock
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
