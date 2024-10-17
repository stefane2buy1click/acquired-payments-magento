<?php

/**
 *
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 *
 *
 */

namespace Acquired\Payments\Exception\Api;

use Magento\Framework\Exception\LocalizedException;

/**
 * @class AuthorizationException
 *
 * Represents an exception for authorization failures during API requests to the Acquired gateway.
 */
class AuthorizationException extends LocalizedException
{
}
