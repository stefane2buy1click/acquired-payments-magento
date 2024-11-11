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

namespace Acquired\Payments\Client;

use Magento\Framework\Exception\LocalizedException;

class PaymentLinks extends AbstractClient
{
    /**
     * Process a payment for method
     *
     * @param array $payload
     * @param string $type
     * @return array|null
     * @throws LocalizedException
     * @throws \Exception
     */
    public function generateLinkId(array $payload): ?array
    {
        return $this->call('post', 'payment-links', $payload);
    }
}
