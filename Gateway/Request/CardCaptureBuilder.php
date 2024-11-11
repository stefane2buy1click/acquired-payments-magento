<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Gateway\Request;

use Exception;
use Psr\Log\LoggerInterface;
use Acquired\Payments\Exception\Command\BuilderException;
use Acquired\Payments\Gateway\Config\Card\Config as CardConfig;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Acquired\Payments\Service\MultishippingService;

class CardCaptureBuilder implements BuilderInterface
{

    /**
     * @param CardConfig $cardConfig
     * @param LoggerInterface $logger
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        private readonly CardConfig $cardConfig,
        private readonly LoggerInterface $logger,
        private readonly CheckoutSession $checkoutSession,
        private readonly MultishippingService $multishippingService
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

            if (empty($payment->getAdditionalInformation('transaction_id')) && !$order->getMultishippingAcquiredTransactionId()) {
                throw new BuilderException(__('Missing transaction_id'));
            }

            return [
                'transaction_id' => ($order->getMultishippingAcquiredTransactionId()) ?: $payment->getAdditionalInformation('transaction_id'),
                'amount' => ['amount' => SubjectReader::readAmount($buildSubject)],
                'is_captured' => $this->cardConfig->getCaptureAction()
            ];
        } catch (Exception $e) {
            $message = __('Capture build failed: %1', $e->getMessage());
            $this->logger->critical($message, ['exception' => $e]);

            throw new BuilderException($message);
        }
    }
}
