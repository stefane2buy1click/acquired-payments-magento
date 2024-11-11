<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
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
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Acquired\Payments\Service\GetTransactionAddressData;
use Acquired\Payments\Api\Data\TransactionAddressDataInterface;

class GetAdminPaymentSessionData implements PaymentSessionDataInterface
{

    /**
     * @param AcquiredCustomerRepositoryInterface $acquiredCustomerRepository
     * @param BackendModelSession $backendQuoteSession
     * @param CardConfig $cardConfig
     * @param SerializerInterface $serializer
     * @param CartRepositoryInterface $cartRepository
     * @param LoggerInterface $logger
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        private readonly AcquiredCustomerRepositoryInterface $acquiredCustomerRepository,
        private readonly BackendModelSession $backendQuoteSession,
        private readonly CardConfig $cardConfig,
        private readonly SerializerInterface $serializer,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly LoggerInterface $logger,
        private readonly PriceCurrencyInterface $priceCurrency,
        private readonly GetTransactionAddressData $getTransactionAddressData
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
            if (!$quote->getReservedOrderId()) {
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
                'amount' => $this->priceCurrency->roundPrice($quote->getGrandTotal()),
                'currency' => strtolower($this->backendQuoteSession->getStore()->getCurrentCurrencyCode()),
                'capture' => $this->cardConfig->getCaptureAction(),
                'moto' => true
            ];

            if ($customData) {
                $payload['transaction']['custom_data'] = base64_encode($this->serializer->serialize($customData));
            }

            $payload['customer'] = [];

            if ($acquiredCustomerId) {
                $payload['customer']['customer_id'] = $acquiredCustomerId;
            }

            $payload['payment_methods'] = ['card']; // only card payments are available for admin orders

            /**
             * @var TransactionAddressDataInterface $addressData
             */
            $addressData = $this->getTransactionAddressData->execute($quote);
            $payload['customer']['billing'] = $addressData->getBilling();
            $payload['customer']['shipping'] = $addressData->getShipping() ?? [];

        } catch (Exception $e) {
            $message = __('Get Admin Payment Session data failed: %1', $e->getMessage());
            $this->logger->critical($message, ['exception' => $e]);

            throw new SessionException(__($e->getMessage()));
        }

        return $payload;
    }
}
