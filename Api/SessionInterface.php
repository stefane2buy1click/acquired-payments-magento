<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Api;

use Acquired\Payments\Api\Data\SessionDataInterface;

interface SessionInterface
{
    /**
     * Create a checkout session with acquired
     *
     * @param string nonce
     * @param mixed $customData
     * @return SessionDataInterface
     */
    public function get(string $nonce, array $customData = null): SessionDataInterface;

    /**
     * Update checkout session data
     *
     * @param string nonce
     * @param string $sessionId
     * @param mixed $customData
     * @return SessionDataInterface
     */
    public function update(string $nonce, string $sessionId, array $customData = null): SessionDataInterface;
}
