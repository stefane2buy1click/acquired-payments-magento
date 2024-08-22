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
 * @class HandlerException
 *
 * Indicates a failure in processing the response from the payment gateway or handling the payment command response.
 */
class HandlerException extends LocalizedException {}
