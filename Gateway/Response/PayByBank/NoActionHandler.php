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

namespace Acquired\Payments\Gateway\Response\PayByBank;

use Acquired\Payments\Exception\Command\HandlerException;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Psr\Log\LoggerInterface;

class NoActionHandler implements HandlerInterface
{

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @throws HandlerException
     */
    public function handle(array $handlingSubject, array $response): void
    {
        // do nothing
    }
}
