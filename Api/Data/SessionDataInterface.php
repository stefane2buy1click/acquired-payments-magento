<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
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
