<?php

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Acquired\Payments\Plugin\Framework;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Session\SessionStartChecker;

class SessionStartCheckerPlugin
{
    /**
     * @var string[]
     */
    private $disableSessionUrls = [
        'acquired/hosted/response'
    ];

    /**
     * @var Http
     */
    private $request;

    /**
     * @param Http $request
     */
    public function __construct(
        Http $request
    ) {
        $this->request = $request;
    }

    public function afterCheck(SessionStartChecker $subject, bool $result): bool
    {
        if ($result === false) {
            return false;
        }

        foreach ($this->disableSessionUrls as $url) {
            if (strpos((string)$this->request->getPathInfo(), $url) !== false) {
                return false;
            }
        }

        return true;
    }
}
