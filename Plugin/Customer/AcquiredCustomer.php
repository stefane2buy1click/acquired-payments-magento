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

namespace Acquired\Payments\Plugin\Customer;

use Acquired\Payments\Api\AcquiredCustomerRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class AcquiredCustomer
{
    /**
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AcquiredCustomerRepositoryInterface $acquiredCustomerRepository
     */
    public function __construct(
        private readonly ExtensionAttributesFactory $extensionFactory,
        private readonly AcquiredCustomerRepositoryInterface $acquiredCustomerRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * After get
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @return CustomerInterface
     */
    public function afterGet(CustomerRepositoryInterface $subject, CustomerInterface $customer)
    {
        $this->setAcquiredCustomerId($customer);
        return $customer;
    }

    /**
     * After get by id
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @return CustomerInterface
     */
    public function afterGetById(CustomerRepositoryInterface $subject, CustomerInterface $customer)
    {
        $this->setAcquiredCustomerId($customer);
        return $customer;
    }

    /**
     * Set Acquired Customer Id to extension attribute value
     *
     * @param CustomerInterface $customer
     * @return void
     */
    private function setAcquiredCustomerId(CustomerInterface $customer): void
    {
        $extensionAttributes = $customer->getExtensionAttributes();
        if ($extensionAttributes === null || $extensionAttributes->getAcquiredCustomerId() === null) {
            try {
                $acquiredCustomer = $this->acquiredCustomerRepository->getByCustomerId((int) $customer->getId());
                $extensionAttributes = $extensionAttributes ?: $this->extensionFactory->create(CustomerInterface::class);
                $customer->setExtensionAttributes($extensionAttributes);

                $extensionAttributes->setAcquiredCustomerId($acquiredCustomer->getAcquiredCustomerId());
            } catch (NoSuchEntityException $e) {
                // do nothing as this causes too much logging otherwise
            }
        }
    }
}
