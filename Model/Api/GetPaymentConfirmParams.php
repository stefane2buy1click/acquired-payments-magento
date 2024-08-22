<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Model\Api;

use Exception;
use Psr\Log\LoggerInterface;
use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Acquired\Payments\Exception\Api\PaymentConfirmParamsException;
use Acquired\Payments\Model\System\Source\PhoneCodesInterface;
use Acquired\Payments\Model\Api\AcquiredSession;

class GetPaymentConfirmParams implements PhoneCodesInterface
{

    /**
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        private readonly CustomerSession $customerSession,
        private readonly CheckoutSession $checkoutSession,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly UrlInterface $urlBuilder,
        private readonly LoggerInterface $logger,
        private readonly AcquiredSession $acquiredSession
    ) {
    }


    /**
     * Get payment confirm parameters
     *
     * @param string $nonce
     * @return array
     * @throws PaymentConfirmParamsException
     */
    public function execute(string $nonce): array
    {

        try {
            $confirmParams = [];

            if (!$this->customerSession->isLoggedIn()) {
                // update acquired session
                $this->acquiredSession->prepareForPurchase($nonce);
                return $confirmParams;
            }

            $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
            $quote = $this->checkoutSession->getQuote();

            $billingAddress = $quote->getBillingAddress();
            $shippingAddress = $quote->getShippingAddress();

            $confirmParams['customer'] = [
                'reference' => $customer->getExtensionAttributes()->getAcquiredCustomerId(),
                'billing' => [
                    'address' => [
                        'line_1' => $billingAddress->getStreetLine(1),
                        'line_2' => $billingAddress->getStreetLine(2),
                        'city' => $billingAddress->getCity(),
                        'postcode' => $billingAddress->getPostcode(),
                        'country_code' => $billingAddress->getCountryId()
                    ],
                    'email' => $billingAddress->getEmail(),
                    'phone' => [
                        'country_code' => $this->getPhoneCodeByCountryId($billingAddress->getCountryId()),
                        'number' => $billingAddress->getTelephone()
                    ]
                ],
                'webhook_url' => $this->urlBuilder->getUrl('acquired/webhook')
            ];

            if ($shippingAddress->getSameAsBilling()) {
                $confirmParams['customer']['shipping']['address_match'] = true;
            } else {
                $confirmParams['customer']['shipping']['address'] = [
                    'line_1' => $shippingAddress->getStreetLine(1),
                    'line_2' => $shippingAddress->getStreetLine(2),
                    'city' => $shippingAddress->getCity(),
                    'postcode' => $shippingAddress->getPostcode(),
                    'country_code' => $shippingAddress->getCountryId()
                ];
            }

            // update acquired session
            $this->acquiredSession->prepareForPurchase($nonce);

            return $confirmParams;

        } catch(Exception $e) {
            $message = __('Get payment confirmation params failed: %1', $e->getMessage());
            $this->logger->critical($message, ['exception' => $e]);

            throw new PaymentConfirmParamsException($message);
        }
    }

    /**
     * @inheritDoc
     */
    public function getPhoneCodeByCountryId($countryId): ?string
    {
        return self::CODES[$countryId] ?? null;
    }
}
