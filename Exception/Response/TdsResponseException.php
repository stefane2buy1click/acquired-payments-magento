<?php

/**
 * Acquired.com Payments Integration for Magento2
 *
 * Copyright (c) 2024 Acquired Limited (https://acquired.com/)
 *
 * This file is open source under the MIT license.
 * Please see LICENSE file for more details.
 */

namespace Acquired\Payments\Exception\Response;

use Magento\Framework\Exception\LocalizedException;

/**
 * @class TdsResponseException
 *
 * Represents an exception for failed integrity checks of 3DS response data.
 */
class TdsResponseException extends LocalizedException
{
}
