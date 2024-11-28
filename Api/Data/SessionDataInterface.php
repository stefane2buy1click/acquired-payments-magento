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

namespace Acquired\Payments\Api\Data;

interface SessionDataInterface
{
    public const SESSION_ID = 'session_id';

    /**
     * Set Session Id
     *
     * @param string $sessionId
     * @return self
     */
    public function setSessionId(string $sessionId): self;

    /**
     * Get Session Id
     *
     * @return string|null
     */
    public function getSessionId(): ?string;
}
