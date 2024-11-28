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

namespace Acquired\Payments\Service;

interface PaymentSessionDataInterface
{
    /**
     * Get data for acquired session
     *
     * @param string $orderId
     * @param array|null $customData
     * @return array
     */
    public function execute(string $orderId, ?array $customData = null): array;
}
