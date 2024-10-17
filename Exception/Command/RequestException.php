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

namespace Acquired\Payments\Exception\Command;

use Magento\Framework\Exception\LocalizedException;

/**
 * @class RequestException
 *
 * Indicates an error during the request generation or processing of the payment commands.
 */

class RequestException extends LocalizedException {}
