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