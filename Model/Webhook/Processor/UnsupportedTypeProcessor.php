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
