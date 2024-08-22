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
 * @class BuilderException
 *
 * Indicates an error within the building process of payment commands.
 */
class BuilderException extends LocalizedException {}
