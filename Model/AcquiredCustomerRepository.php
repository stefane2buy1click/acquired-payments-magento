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

namespace Acquired\Payments\Model;

use Acquired\Payments\Api\AcquiredCustomerRepositoryInterface;
use Acquired\Payments\Api\Data\AcquiredCustomerInterface;
use Acquired\Payments\Model\ResourceModel\AcquiredCustomer\Collection;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Acquired\Payments\Model\AcquiredCustomerFactory;
use Acquired\Payments\Model\ResourceModel\AcquiredCustomer\CollectionFactory;
use Acquired\Payments\Model\ResourceModel\AcquiredCustomer as AcquiredCustomerResource;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class AcquiredCustomerRepository implements AcquiredCustomerRepositoryInterface
{

    /**
     * @param AcquiredCustomerFactory $acquiredCustomerFactory
     * @param AcquiredCustomerResource $acquiredCustomerResource
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchResultFactory $searchResultFactory
     */
    public function __construct(
        private readonly AcquiredCustomerFactory $acquiredCustomerFactory,
        private readonly AcquiredCustomerResource $acquiredCustomerResource,
        private readonly CollectionFactory $collectionFactory,
        private readonly CollectionProcessorInterface $collectionProcessor,
        private readonly SearchResultFactory $searchResultFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getById(string $acquiredCustomerId): AcquiredCustomerInterface
    {
        return $this->get($acquiredCustomerId, AcquiredCustomerInterface::ACQUIRED_CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function getByCustomerId(int $customerId): AcquiredCustomerInterface
    {
        return $this->get($customerId, AcquiredCustomerInterface::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $criteria): SearchResultInterface
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($criteria, $collection);
        /** @var  $searchResults */
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function save(AcquiredCustomerInterface $acquiredCustomer): AcquiredCustomerInterface
    {
        try {
            $this->acquiredCustomerResource->save($acquiredCustomer);
        } catch (AlreadyExistsException) {
            throw new CouldNotSaveException(
                __('Acquired customer with id: %1 already exists', $acquiredCustomer->getAcquiredCustomerId())
            );
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $acquiredCustomer;
    }

    /**
     * Get entity
     *
     * @param string|int $id
     * @param string $field
     * @throws NoSuchEntityException
     *
     * @return AcquiredCustomerInterface
     */
    private function get(string|int $id, string $field): AcquiredCustomerInterface
    {

        $acquiredCustomer = $this->acquiredCustomerFactory->create();
        $this->acquiredCustomerResource->load($acquiredCustomer, $id, $field);

        if (empty($acquiredCustomer->getId())) {
            throw new NoSuchEntityException(
                __('No such entity with %1: %2', $field, $id)
            );
        }

        return $acquiredCustomer;
    }
}
