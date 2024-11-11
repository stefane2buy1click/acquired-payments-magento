<?php

/**
 * Acquired.com Payments Integration for Magento2
 *
 * Copyright (c) 2024 Acquired Limited (https://acquired.com/)
 *
 * This file is open source under the MIT license.
 * Please see LICENSE file for more details.
 */

namespace Acquired\Payments\Model\Webhook\Processor;

/**
 * @class UnsupportedTypeProcessor
 *
 * Handles unsupported webhook types by returning a standardized response.
 */
class UnsupportedTypeProcessor extends AbstractProcessor
{

    /**
     * Processes unsupported webhook types.
     *
     * @param array $webhookData The entire payload of the webhook data.
     * @param string $webhookHash The hash received in the webhook header for integrity validation.
     * @param string $webhookVersion The version of the webhook provided in the webhook header.
     * @return array Processing result including success status and a message.
     */
    public function process(array $webhookData, string $webhookHash, string $webhookVersion): array
    {
        return [
            'success' => false,
            'message' => __('Webhook type %1 is not supported.', $webhookData['webhook_type'])
        ];
    }
}
