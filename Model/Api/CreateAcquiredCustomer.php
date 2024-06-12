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

namespace Acquired\Payments\Model\Api;

use Acquired\Payments\Api\AcquiredCustomerRepositoryInterface;
use Acquired\Payments\Client\Gateway;
use Acquired\Payments\Model\AcquiredCustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class CreateAcquiredCustomer
{

    /**
     * @param CustomerSession $customerSession
     * @param Gateway $gateway
     * @param AcquiredCustomerFactory $acquiredCustomerFactory
     * @param AcquiredCustomerRepositoryInterface $acquiredCustomerRepository
     */
    public function __construct(
        private readonly CustomerSession $customerSession,
        private readonly Gateway $gateway,
        private readonly AcquiredCustomerFactory $acquiredCustomerFactory,
        private readonly AcquiredCustomerRepositoryInterface $acquiredCustomerRepository
    ) {
    }

    /**
     * Initialize acquired customer
     *
     * @return array|null
     * @throws LocalizedException
     */
    public function execute(): ?array
    {
        if ($this->customerSession->isLoggedIn()) {
            try {
                $acquiredCustomerId = $this->acquiredCustomerRepository->getByCustomerId(
                    (int) $this->customerSession->getCustomerId()
                )->getAcquiredCustomerId();
                return $this->gateway->getCustomer()->get($acquiredCustomerId);
            } catch (NoSuchEntityException) {
                return $this->createCustomer();
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Create acquired customer
     *
     * @return array
     * @throws LocalizedException
     */
    private function createCustomer(): array
    {
        try {
            $customerData = $this->getCustomerData();
            $result = $this->gateway->getCustomer()->create($customerData);

            $acquiredCustomer = $this->acquiredCustomerFactory->create();
            $acquiredCustomer->setCustomerId((int) $this->customerSession->getCustomerId())
                ->setAcquiredCustomerId($result['customer_id']);
            $this->acquiredCustomerRepository->save($acquiredCustomer);

            return $result;
        } catch (CouldNotSaveException $exception) {
            throw new LocalizedException(__('Could not save customer'));
        } catch (\Exception $exception) {
            throw new LocalizedException(__('There was an issue processing request'));
        }
    }

    /**
     * Get customer data
     *
     * @return array
     */
    public function getCustomerData(): array
    {
        if (!$this->customerSession->isLoggedIn()) {
            return [];
        }

        $customer = $this->customerSession->getCustomer();
        $customerData = [
            'reference' => $customer->getId(),
            'first_name' => $customer->getFirstname(),
            'last_name' => $customer->getLastname()
        ];

        if ($customer->getDob()) {
            $customerData['dob'] = date('Y-m-d', strtotime($customer->getDob()));
        }

        return $customerData;
    }
}
