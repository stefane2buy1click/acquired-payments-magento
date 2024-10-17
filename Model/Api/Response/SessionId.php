<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
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
