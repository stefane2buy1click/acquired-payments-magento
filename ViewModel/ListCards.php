<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\ViewModel;

use Acquired\Payments\Api\AcquiredCustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Acquired\Payments\Client\Gateway;

class ListCards implements ArgumentInterface
{
    /**
     * SaveCards Constructor
     *
     * @param CustomerSession $customerSession
     * @param AcquiredCustomerRepositoryInterface $acquiredCustomerRepository
     * @param Gateway $gateway
     */
    public function __construct(
        private readonly CustomerSession $customerSession,
        private readonly AcquiredCustomerRepositoryInterface $acquiredCustomerRepository,
        private readonly Gateway $gateway
    ) {
    }

    /**
     * Get saved cards against customer
     *
     * @return array
     */
    public function getCustomerCards(): array
    {
        $cards = [];
        $customerId = (int)$this->customerSession->getCustomerId();

        try {
            $acquiredCustomer = $this->acquiredCustomerRepository->getByCustomerId($customerId);
            $cards = $this->gateway->getCustomer()->listCards($acquiredCustomer->getAcquiredCustomerId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        }

        return $cards;
    }
}