<?php

/**
 * Acquired.com Payments Integration for Magento2
 *
 * Copyright (c) 2024 Acquired Limited (https://acquired.com/)
 *
 * This file is open source under the MIT license.
 * Please see LICENSE file for more details.
 */

namespace Acquired\Payments\Exception\Api;

use Magento\Framework\Exception\LocalizedException;

/**
 * @class InvalidMethodException
 *
 * Represents an exception for invalid method usage in API requests to the Acquired gateway.
 */
class InvalidMethodException extends LocalizedException
{
}
