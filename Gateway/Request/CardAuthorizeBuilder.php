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
use Magento\Checkout\Model\Session as CheckoutSession;
use Acquired\Payments\Service\MultishippingService;

class CardAuthorizeBuilder implements BuilderInterface
{

    /**
     * @param LoggerInterface $logger
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CheckoutSession $checkoutSession,
        private readonly MultishippingService $multishippingService
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

            if (empty($payment->getAdditionalInformation('transaction_id')) && !$order->getMultishippingAcquiredTransactionId()) {
                throw new BuilderException(__('Missing transaction_id'));
            }

            return [
                'transaction_id' => ($order->getMultishippingAcquiredTransactionId()) ?: $payment->getAdditionalInformation('transaction_id')
            ];

        } catch (Exception $e) {
            $message = __('Authorize build failed: %1', $e->getMessage());
            $this->logger->critical($message, ['exception' => $e]);

            throw new BuilderException($message);
        }
    }
}
