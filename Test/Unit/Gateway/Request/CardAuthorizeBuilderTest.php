<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

use PHPUnit\Framework\TestCase;
use Acquired\Payments\Gateway\Request\CardAuthorizeBuilder;
use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Acquired\Payments\Service\MultishippingService;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order;
use Acquired\Payments\Exception\Command\BuilderException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class CardAuthorizeBuilderTest extends TestCase
{
    private $loggerMock;
    private $checkoutSessionMock;
    private $multishippingServiceMock;
    private $cardAuthorizeBuilder;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->checkoutSessionMock = $this->createMock(CheckoutSession::class);
        $this->multishippingServiceMock = $this->createMock(MultishippingService::class);

        $this->cardAuthorizeBuilder = new CardAuthorizeBuilder(
            $this->loggerMock,
            $this->checkoutSessionMock,
            $this->multishippingServiceMock
        );
    }

    public function testBuildSuccess()
    {
        $paymentDataMock = $this->createMock(PaymentDataObjectInterface::class);
        $paymentMock = $this->createMock(Payment::class);
        $orderMock = $this->createMock(Order::class);
        $paymentMock->method('getOrder')->willReturn($orderMock);
        $paymentMock->method('getAdditionalInformation')->with('transaction_id')->willReturn('123456');
        $paymentDataMock->method('getPayment')->willReturn($paymentMock);
        $paymentDataMock->method('getOrder')->willReturn($orderMock);

        $buildSubject = ['payment' => $paymentDataMock];

        $result = $this->cardAuthorizeBuilder->build($buildSubject);

        $this->assertEquals(['transaction_id' => '123456'], $result);
    }

    public function testBuildMissingTransactionId()
    {
        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('Missing transaction_id');

        $paymentDataMock = $this->createMock(PaymentDataObjectInterface::class);
        $paymentMock = $this->createMock(Payment::class);
        $orderMock = $this->createMock(Order::class);
        $paymentMock->method('getOrder')->willReturn($orderMock);
        $paymentMock->method('getAdditionalInformation')->with('transaction_id')->willReturn(null);
        $paymentDataMock->method('getPayment')->willReturn($paymentMock);
        $paymentDataMock->method('getOrder')->willReturn($orderMock);

        $buildSubject = ['payment' => $paymentDataMock];

        $this->cardAuthorizeBuilder->build($buildSubject);
    }

    public function testBuildExceptionHandling()
    {
        $this->expectException(BuilderException::class);

        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->method('getOrder')->willThrowException(new \Exception('Test Exception'));

        $buildSubject = ['payment' => $paymentMock];

        $this->loggerMock->expects($this->once())->method('critical');

        $this->cardAuthorizeBuilder->build($buildSubject);
    }
}
