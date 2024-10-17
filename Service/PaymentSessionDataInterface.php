<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
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
