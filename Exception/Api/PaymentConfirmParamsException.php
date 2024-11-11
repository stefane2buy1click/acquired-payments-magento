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
 * @class PaymentConfirmParamsException
 *
 * Indicates a failure in generating payment confirmation parameters for Acquired API transactions.
 */
class PaymentConfirmParamsException extends LocalizedException
{
}
