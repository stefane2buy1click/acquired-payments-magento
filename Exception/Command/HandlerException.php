<?php

/**
 * Acquired.com Payments Integration for Magento2
 *
 * Copyright (c) 2024 Acquired Limited (https://acquired.com/)
 *
 * This file is open source under the MIT license.
 * Please see LICENSE file for more details.
 */

namespace Acquired\Payments\Exception\Command;

use Magento\Framework\Exception\LocalizedException;

/**
 * @class HandlerException
 *
 * Indicates a failure in processing the response from the payment gateway or handling the payment command response.
 */
class HandlerException extends LocalizedException
{
}
