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

namespace Acquired\Payments\Model\Data;

use Acquired\Payments\Api\Data\TransactionAddressDataInterface;

/**
 *
 * Provides a readonly data model for transaction address data
 */
class TransactionAddressData implements TransactionAddressDataInterface
{
    /**
     * @var array
     */
    private array $billing;

    /**
     * @var array|null
     */
    private ?array $shipping;

    /**
     * @param array $billing
     * @param array|null $shipping
     */
    public function __construct(
        array $billing,
        ?array $shipping
    ) {
        $this->billing = $billing;
        $this->shipping = $shipping;
    }

    /**
     * Gets transaction billing address data
     *
     * @return array
     */
    public function getBilling(): array
    {
        return $this->billing;
    }

    /**
     * Get transaction shipping address data or null for virtual orders
     *
     * @return array|null
     */
    public function getShipping(): ?array
    {
        return $this->shipping;
    }

}