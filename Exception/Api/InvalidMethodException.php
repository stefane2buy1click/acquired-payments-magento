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

namespace Acquired\Payments\Exception\Api;

use Magento\Framework\Exception\LocalizedException;

/**
 * @class InvalidMethodException
 *
 * Represents an exception for invalid method usage in API requests to the Acquired gateway.
 */
class InvalidMethodException extends LocalizedException {}
