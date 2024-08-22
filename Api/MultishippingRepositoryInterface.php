<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface MultishippingRepositoryInterface
{

    /**
     * Save Multishipping
     * @param \Acquired\Payments\Api\Data\MultishippingInterface $multishipping
     * @return \Acquired\Payments\Api\Data\MultishippingInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Acquired\Payments\Api\Data\MultishippingInterface $multishipping
    );

    /**
     * Retrieve Multishipping
     * @param string $multishippingId
     * @return \Acquired\Payments\Api\Data\MultishippingInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($multishippingId);

    /**
     * Retrieve Multishipping matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Acquired\Payments\Api\Data\MultishippingSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Multishipping
     * @param \Acquired\Payments\Api\Data\MultishippingInterface $multishipping
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Acquired\Payments\Api\Data\MultishippingInterface $multishipping
    );

    /**
     * Delete Multishipping by ID
     * @param string $multishippingId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($multishippingId);
}