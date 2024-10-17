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

use Acquired\Payments\Gateway\Config\Basic;
use Magento\Framework\Serialize\SerializerInterface;
use Acquired\Payments\Model\Webhook\Context as WebhookContext;
use Acquired\Payments\Service\MultishippingService;

/**
 * @class StatusUpdateProcessor
 *
 * Handles processing of "status_update" webhook notifications with integrity validation for normal orders.
 */
class CompositeStatusUpdateProcessor extends AbstractProcessor
{

    /**
     * @param Basic $basicConfig
     * @param SerializerInterface $serializer
     */
    public function __construct(
        protected readonly Basic $basicConfig,
        protected readonly SerializerInterface $serializer,
        protected readonly WebhookContext $webhookContext,
        protected readonly OrderStatusUpdateProcessor $orderStatusUpdateProcessor,
        protected readonly MultishippingStatusUpdateProcessor $multishippingStatusUpdateProcessor
    ) {
    }

    /**
     * Processes webhook data by validating its version and integrity, then acts based on its type and status.
     *
     * @param array $webhookData The entire payload of the webhook data.
     * @param string $webhookHash The hash received in the webhook header for integrity validation.
     * @param string $webhookVersion The version of the webhook provided in the webhook header.
     * @return array Processing result including success status and a message.
     */
    public function process(array $webhookData, string $webhookHash, string $webhookVersion): array
    {
        $webhookBody = $webhookData['webhook_body'];

        if ($this->isMultishippingOrder($webhookBody)) {
            return $this->multishippingStatusUpdateProcessor->process($webhookData, $webhookHash, $webhookVersion);
        } else {
            return $this->orderStatusUpdateProcessor->process($webhookData, $webhookHash, $webhookVersion);
        }
    }

    protected function isMultishippingOrder($webhookBody)
    {
        return strpos($webhookBody['order_id'], MultishippingService::MULTISHIPPING_ORDER_ID_SUFFIX) !== false;
    }
}
