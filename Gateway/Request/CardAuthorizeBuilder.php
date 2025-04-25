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
use Magento\Checkout\Model\Session as CheckoutSession;
use Acquired\Payments\Service\MultishippingService;
use Acquired\Payments\Gateway\Validator\RequestBuilderValidator;

class CardAuthorizeBuilder implements BuilderInterface
{

    /**
     * @param LoggerInterface $logger
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CheckoutSession $checkoutSession,
        private readonly MultishippingService $multishippingService,
        private readonly RequestBuilderValidator $requestBuilderValidator
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

            $paymentTransactionId = $payment->getAdditionalInformation('transaction_id');
            if (empty($paymentTransactionId) && !$order->getMultishippingAcquiredTransactionId()) {
                throw new BuilderException(__('Missing transaction_id'));
            }

            $transactionId = ($order->getMultishippingAcquiredTransactionId()) ?: $paymentTransactionId;

            if (!$paymentTransactionId) {
                $payment->setAdditionalInformation('transaction_id', $transactionId);
            }
            $this->requestBuilderValidator->validate($payment->getAdditionalInformation());

            return [
                'transaction_id' => $transactionId
            ];
        } catch (Exception $e) {
            $message = __('Authorize build failed: %1', $e->getMessage());
            $this->logger->critical($message, ['exception' => $e]);

            throw new BuilderException($message);
        }
    }
}
