<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Api;

use Acquired\Payments\Api\Data\AcquiredCustomerInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface AcquiredCustomerRepositoryInterface
{
    /**
     * Get by Acquired Customer Id
     *
     * @param string $acquiredCustomerId
     * @throws NoSuchEntityException
     *
     * @return AcquiredCustomerInterface
     */
    public function getById(string $acquiredCustomerId): AcquiredCustomerInterface;

    /**
     * Get by Customer Id
     *
     * @param int $customerId
     * @throws NoSuchEntityException
     *
     * @return AcquiredCustomerInterface
     */
    public function getByCustomerId(int $customerId): AcquiredCustomerInterface;

    /**
     * Get list
     *
     * @param SearchCriteriaInterface $criteria
     * @return SearchResultInterface
     */
    public function getList(SearchCriteriaInterface $criteria): SearchResultInterface;

    /**
     * Save acquired customer
     *
     * @param AcquiredCustomerInterface $acquiredCustomer
     * @throws CouldNotSaveException
     *
     * @return AcquiredCustomerInterface
     */
    public function save(AcquiredCustomerInterface $acquiredCustomer): AcquiredCustomerInterface;
}
