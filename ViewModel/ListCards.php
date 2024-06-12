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