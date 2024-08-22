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
