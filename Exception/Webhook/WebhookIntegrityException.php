<?php

/**
 * Acquired.com Payments Integration for Magento2
 *
 * Copyright (c) 2024 Acquired Limited (https://acquired.com/)
 *
 * This file is open source under the MIT license.
 * Please see LICENSE file for more details.
 */

namespace Acquired\Payments\Exception\Webhook;

use Magento\Framework\Exception\LocalizedException;

/**
 * @class WebhookIntegrityException
 *
 * Represents an exception for failed integrity checks of webhook data.
 */
class WebhookIntegrityException extends LocalizedException {}
