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

class MerchantSession extends AbstractClient
{
    /**
     * Create a merchant session for applepay
     *
     * @param array $payload
     * @return array|null
     * @throws \Exception
     */
    public function applePay(array $payload): ?array
    {
        return $this->call('post', 'payment-methods/apple-pay/session', $payload);
    }
}
