<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
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
