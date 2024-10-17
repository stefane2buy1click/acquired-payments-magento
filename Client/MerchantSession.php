<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
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
