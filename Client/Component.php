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

class Component extends AbstractClient
{
    /**
     * Create payment session
     *
     * @param array $payload
     * @return array|null
     * @throws \Exception
     */
    public function create(array $payload): ?array
    {
        return $this->call('post', 'payment-sessions', $payload);
    }

    /**
     * Update payment session
     *
     * @param string $sessionId
     * @param array $payload
     * @return array|null
     * @throws \Exception
     */
    public function update(string $sessionId, array $payload): ?array
    {
        return $this->call('put', "payment-sessions/$sessionId", $payload);
    }
}
