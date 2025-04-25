<?php declare(strict_types=1);

/**
 * Acquired.com Payments Integration for Magento2
 *
 * Copyright (c) 2024 Acquired Limited (https://acquired.com/)
 *
 * This file is open source under the MIT license.
 * Please see LICENSE file for more details.
 */

namespace Acquired\Payments\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\TestCase;

abstract class AbstractBuilderTestCase extends TestCase {

    protected function getPaymentMock(?float $amount, ?string $transactionId, string $incrementId) : PaymentDataObjectInterface {
        return $this->getPaymentDataObjectMock(
            $this->getOrderPaymentMock($amount, $transactionId, $incrementId)
        );
    }

    protected function getOrderPaymentMock(?float $amount = null, ?string $transactionId = null, ?string $incrementId = null) : Payment {
        $orderMock = $this->createMock(Order::class);
        $orderMock->method('getGrandTotal')->willReturn($amount);
        $orderMock->method('getIncrementId')->willReturn($incrementId);

        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->method('getOrder')->willReturn($orderMock);
        $paymentMock->method('getAdditionalInformation')->willReturnCallback(function ($key) use ($transactionId, $incrementId) {
            $data = [
                "transaction_id" => $transactionId,
                "order_id" => $incrementId,
                "timestamp" => null,
                "hash" => null
            ];

            if(empty($key)) {
                return $data;
            }

            return isset($data[$key]) ? $data[$key] : null;
        });
        $paymentMock->method('getLastTransId')->willReturn($transactionId);

        return $paymentMock;
    }

    protected function getPaymentDataObjectMock(Payment $payment): PaymentDataObjectInterface
    {
        $paymentDataObjectMock = $this->createMock(PaymentDataObjectInterface::class);
        $paymentDataObjectMock->method('getPayment')->willReturn($payment);
        return $paymentDataObjectMock;
    }

}