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

use Acquired\Payments\Gateway\Request\CardAuthorizeBuilder;
use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Acquired\Payments\Service\MultishippingService;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order;
use Acquired\Payments\Exception\Command\BuilderException;
use Acquired\Payments\Test\Unit\Gateway\Request\AbstractBuilderTestCase;

class CardAuthorizeBuilderTest extends AbstractBuilderTestCase
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
        $transactionId = '123456';
        $paymentDataMock = $this->getPaymentMock(100.00, $transactionId, '100000001');
        $buildSubject = ['payment' => $paymentDataMock];

        $result = $this->cardAuthorizeBuilder->build($buildSubject);

        $this->assertEquals(['transaction_id' => $transactionId], $result);
    }

    public function testBuildMissingTransactionId()
    {
        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('Missing transaction_id');

        $transactionId = null;
        $paymentDataMock = $this->getPaymentMock(100.00, $transactionId, '100000001');
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
