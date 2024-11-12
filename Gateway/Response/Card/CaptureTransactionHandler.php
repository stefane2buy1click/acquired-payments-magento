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

namespace Acquired\Payments\Gateway\Response\Card;

use Exception;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Acquired\Payments\Exception\Command\HandlerException;
use Psr\Log\LoggerInterface;

class CaptureTransactionHandler implements HandlerInterface
{

    /**
     * @param LoggerInterface $logger
     * @param Gateway $gateway
     */
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @throws HandlerException
     */
    public function handle(array $handlingSubject, array $response): void
    {
        try {
            /** @var OrderPaymentInterface $payment */
            $payment = SubjectReader::readPayment($handlingSubject)->getPayment();

            $this->setTransactionDataToPayment($payment, $response);

            /** if this was auth only then no need to set additional data again, as they will be empty **/
            if (isset($response['payment_method'])) {
                $this->setAdditionalTransactionData($payment, $response);
            }
        } catch (Exception $e) {
            $message = __('Capture Handler failed: %1', $e->getMessage());
            $this->logger->critical($message, ['exception' => $e]);

            throw new HandlerException($message);
        }
    }

    /**
     * Set Transaction data to payment
     *
     * @param OrderPaymentInterface $payment
     * @param array $transaction
     */
    private function setTransactionDataToPayment(OrderPaymentInterface $payment, array $transaction): void
    {
        $payment->setLastTransId($transaction['transaction_id']);
        $payment->setTransactionId($transaction['transaction_id']);
        if (!empty($transaction['card'])) {
            $payment->setCcType($transaction['card']['scheme']);
            $payment->setCcLast4($transaction['card']['number']);
            $payment->setCcExpMonth($transaction['card']['expiry_month']);
            $payment->setCcExpYear($transaction['card']['expiry_year']);
        }
    }

    /**
     * Set additional transaction data to payment additional information
     *
     * @param OrderPaymentInterface $payment
     * @param array $transaction
     * @return void
     */
    private function setAdditionalTransactionData(OrderPaymentInterface $payment, array $transaction): void
    {
        $payment->setAdditionalInformation('payment_method', $transaction['payment_method']);
        $payment->setAdditionalInformation('mid', $transaction['mid']);
        $payment->setAdditionalInformation('transaction_id', $transaction['transaction_id']);
        $payment->setAdditionalInformation('authorization_code', $transaction['authorization_code']);

        if (!empty($transaction['card'])) {
            $payment->setAdditionalInformation('cc_type', $transaction['card']['scheme']);
            $payment->setAdditionalInformation('holder_name', $transaction['card']['holder_name']);
            $payment->setAdditionalInformation('cc_last4', $transaction['card']['number']);
            $payment->setAdditionalInformation(
                'cc_exp',
                $transaction['card']['expiry_month'] . '/' . $transaction['card']['expiry_year']
            );
        }

        if (!empty($transaction['check'])) {
            $payment->setAdditionalInformation('avs_line1', $transaction['check']['avs_line1']);
            $payment->setAdditionalInformation('avs_postcode', $transaction['check']['avs_postcode']);
            $payment->setAdditionalInformation('cvv', $transaction['check']['cvv']);
        }
    }
}
