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

namespace Acquired\Payments\Model\Api\Response;

use Acquired\Payments\Api\Data\SessionDataInterface;
use Magento\Framework\DataObject;

class SessionId extends DataObject implements SessionDataInterface
{
    /**
     * @inheritDoc
     */
    public function setSessionId(string $sessionId): SessionId
    {
        return $this->setData(self::SESSION_ID, $sessionId);
    }

    /**
     * @inheritDoc
     */
    public function getSessionId(): ?string
    {
        return $this->getData(self::SESSION_ID);
    }
}
