<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Model;

use Acquired\Payments\Api\Data\MultishippingInterface;
use Acquired\Payments\Api\Data\MultishippingInterfaceFactory;
use Acquired\Payments\Api\Data\MultishippingSearchResultsInterfaceFactory;
use Acquired\Payments\Api\MultishippingRepositoryInterface;
use Acquired\Payments\Model\ResourceModel\Multishipping as ResourceMultishipping;
use Acquired\Payments\Model\ResourceModel\Multishipping\CollectionFactory as MultishippingCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class MultishippingRepository implements MultishippingRepositoryInterface
{

    /**
     * @var ResourceMultishipping
     */
    protected $resource;

    /**
     * @var MultishippingInterfaceFactory
     */
    protected $multishippingFactory;

    /**
     * @var MultishippingCollectionFactory
     */
    protected $multishippingCollectionFactory;

    /**
     * @var Multishipping
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;


    /**
     * @param ResourceMultishipping $resource
     * @param MultishippingInterfaceFactory $multishippingFactory
     * @param MultishippingCollectionFactory $multishippingCollectionFactory
     * @param MultishippingSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceMultishipping $resource,
        MultishippingInterfaceFactory $multishippingFactory,
        MultishippingCollectionFactory $multishippingCollectionFactory,
        MultishippingSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->multishippingFactory = $multishippingFactory;
        $this->multishippingCollectionFactory = $multishippingCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(MultishippingInterface $multishipping)
    {
        try {
            $this->resource->save($multishipping);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the multishipping: %1',
                $exception->getMessage()
            ));
        }
        return $multishipping;
    }

    /**
     * @inheritDoc
     */
    public function get($multishippingId)
    {
        $multishipping = $this->multishippingFactory->create();
        $this->resource->load($multishipping, $multishippingId);
        if (!$multishipping->getId()) {
            throw new NoSuchEntityException(__('Multishipping with id "%1" does not exist.', $multishippingId));
        }
        return $multishipping;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->multishippingCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(MultishippingInterface $multishipping)
    {
        try {
            $multishippingModel = $this->multishippingFactory->create();
            $this->resource->load($multishippingModel, $multishipping->getMultishippingId());
            $this->resource->delete($multishippingModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Multishipping: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($multishippingId)
    {
        return $this->delete($this->get($multishippingId));
    }
}