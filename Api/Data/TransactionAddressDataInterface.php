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

interface TransactionAddressDataInterface
{

    /**
     * Gets transaction billing address data
     *
     * @return array
     */
    public function getBilling() : array;

    /**
     * Get transaction shipping address data or null for virtual orders
     *
     * @return array|null
     */
    public function getShipping(): ?array;
}
