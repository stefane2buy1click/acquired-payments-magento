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

namespace Acquired\Payments\Service;

use Exception;
use Psr\Log\LoggerInterface;
use Acquired\Payments\Api\AcquiredCustomerRepositoryInterface;
use Acquired\Payments\Gateway\Config\Card\Config as CardConfig;
use Magento\Backend\Model\Session\Quote as BackendModelSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Acquired\Payments\Exception\Api\SessionException;

class GetAdminPaymentSessionData implements PaymentSessionDataInterface
{

    /**
     * @param AcquiredCustomerRepositoryInterface $acquiredCustomerRepository
     * @param BackendModelSession $backendQuoteSession
     * @param CardConfig $cardConfig
     * @param SerializerInterface $serializer
     * @param CartRepositoryInterface $cartRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly AcquiredCustomerRepositoryInterface $acquiredCustomerRepository,
        private readonly BackendModelSession $backendQuoteSession,
        private readonly CardConfig $cardConfig,
        private readonly SerializerInterface $serializer,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Get data for admin place order
     *
     * @param string $orderId
     * @param array|null $customData
     * @return array
     * @throws SessionException
     */
    public function execute(string $orderId, ?array $customData = null): array
    {
        try {
            $quote = $this->backendQuoteSession->getQuote();
            if(!$quote->getReservedOrderId()) {
                $quote->reserveOrderId();
                $this->cartRepository->save($quote);
            }

            $customerId = (int) $this->backendQuoteSession->getCustomerId();
            try {
                $acquiredCustomerId = $this->acquiredCustomerRepository
                    ->getByCustomerId($customerId)->getAcquiredCustomerId();
            } catch (NoSuchEntityException) {
                $acquiredCustomerId = null;
            }

            $payload['transaction'] = [
                'order_id' => $quote->getReservedOrderId(),
                'amount' => number_format((float) $quote->getGrandTotal(), 2, '.', ''),
                'currency' => strtolower($this->backendQuoteSession->getStore()->getCurrentCurrencyCode()),
                'capture' => $this->cardConfig->getCaptureAction(),
                'moto' => true
            ];

            if ($customData) {
                $payload['transaction']['custom_data'] = base64_encode($this->serializer->serialize($customData));
            }

            if ($acquiredCustomerId) {
                $payload['customer'] = [
                    'customer_id' => $acquiredCustomerId
                ];
            }
        } catch (Exception $e) {
            $message = __('Get Admin Payment Session data failed: %1', $e->getMessage());
            $this->logger->critical($message, ['exception' => $e]);

            throw new SessionException(__($e->getMessage()));
        }

        return $payload;
    }
}
