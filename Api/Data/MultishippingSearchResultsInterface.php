<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Api\Data;

interface MultishippingSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Multishipping list.
     * @return \Acquired\Payments\Api\Data\MultishippingInterface[]
     */
    public function getItems();

    /**
     * Set id list.
     * @param \Acquired\Payments\Api\Data\MultishippingInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}