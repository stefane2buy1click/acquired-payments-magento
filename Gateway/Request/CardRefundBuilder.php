<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Gateway\Request;


use Exception;
use Psr\Log\LoggerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Acquired\Payments\Exception\Command\BuilderException;

class CardRefundBuilder implements BuilderInterface
{

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly LoggerInterface $logger
    ){
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
            $order = $payment->getOrder();
            $amount = (float)SubjectReader::readAmount($buildSubject);

            if ($amount <= 0) {
                throw new BuilderException(__('Refunds cannot be processed if the amount is 0. Please specify a different amount.'));
            }

            $transactionId = $payment->getAdditionalInformation('transaction_id') ?: $payment->getLastTransId();

            if (empty($transactionId)) {
                throw new BuilderException(__('Missing transaction_id'));
            }

            return [
                'transaction_id' => $transactionId,
                'grand_total' => $order->getGrandTotal(),
                'reference' => [
                    'reference' => $payment->getOrder()?->getIncrementId(),
                    'amount' => number_format((float) $amount, 2, '.', '')
                ]
            ];

        } catch (Exception $e) {
            $message = __('Refund build failed: %1', $e->getMessage());
            $this->logger->critical($message, ['exception' => $e]);

            throw new BuilderException($message);
        }
    }
}
