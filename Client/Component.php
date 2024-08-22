<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
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
