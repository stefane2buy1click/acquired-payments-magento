<?php

/**
 *
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 *
 *
 */

namespace Acquired\Payments\Logger;

use Monolog\Logger as MonologLogger;
use Acquired\Payments\Gateway\Config\Basic;

/**
 * @class Logger
 *
 * Provides logging capabilities for the Acquired Payments module.
 */
class Logger extends MonologLogger
{

    /**
     *
     * @param string $name
     * @param Basic $basicConfig
     * @param array $handlers
     */
    public function __construct(
        string$name,
        private readonly Basic $basicConfig,
        array $handlers = []

    ) {
        parent::__construct($name, $handlers);
    }

    /**
     * Logs with DEBUG level if debug mode is enabled.
     *
     * @param string $message The log message.
     * @param array $context Additional information for log processors and handlers.
     */
    public function debug($message, array $context = []): void
    {
        if ($this->basicConfig->isDebugLogEnabled()) {
            parent::debug($message, $context);
        }
    }

    /**
     * Logs with INFO level if debug mode is enabled.
     *
     * @param string $message The log message.
     * @param array $context Additional information for log processors and handlers.
     */
    public function info($message, array $context = []): void
    {
        if ($this->basicConfig->isDebugLogEnabled()) {
            parent::info($message, $context);
        }
    }

    /**
     * Logs with ERROR level.
     *
     * @param string $message The log message.
     * @param array $context Additional information for log processors and handlers.
     */
    public function error($message, array $context = []): void
    {
        parent::error($message, $context);
    }

    /**
     * Logs with CRITICAL level without checking if debug mode is enabled.
     *
     * @param string $message The log message.
     * @param array $context Additional information for log processors and handlers.
     */
    public function critical($message, array $context = []): void
    {
        parent::critical($message, $context);
    }
}
