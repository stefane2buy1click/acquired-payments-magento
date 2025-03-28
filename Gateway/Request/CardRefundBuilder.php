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

namespace Acquired\Payments\Gateway\Request;


use Exception;
use Psr\Log\LoggerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Acquired\Payments\Exception\Command\BuilderException;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class CardRefundBuilder implements BuilderInterface
{

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly PriceCurrencyInterface $priceCurrency
    ) {
    }

    /**
     * @param array $buildSubject
     * @return array
     * @throws BuilderException
     */
    public function build(array $buildSubject): array
    {
        try {
            $payment = SubjectReader::readPayment($buildSubject)->getPayment();
            $order = $payment instanceof \Magento\Sales\Model\Order\Payment ? $payment->getOrder() : SubjectReader::readPayment($buildSubject)->getOrder();
            $paymentTransactionId = $payment instanceof \Magento\Sales\Model\Order\Payment ? $payment->getLastTransId() : $payment->getAdditionalInformation('transaction_id');
            $amount = (float)SubjectReader::readAmount($buildSubject);

            if ($amount <= 0) {
                throw new BuilderException(__('Refunds cannot be processed if the amount is 0. Please specify a different amount.'));
            }

            $transactionId = $payment->getAdditionalInformation('transaction_id') ?: $paymentTransactionId;

            if (empty($transactionId)) {
                throw new BuilderException(__('Missing transaction_id'));
            }

            return [
                'transaction_id' => $transactionId,
                'grand_total' => $order->getGrandTotal(),
                'reference' => [
                    'reference' => $order?->getIncrementId(),
                    'amount' => $this->priceCurrency->round($amount)
                ]
            ];
        } catch (Exception $e) {
            $message = __('Refund build failed: %1', $e->getMessage());
            $this->logger->critical($message, ['exception' => $e]);

            throw new BuilderException($message);
        }
    }
}
