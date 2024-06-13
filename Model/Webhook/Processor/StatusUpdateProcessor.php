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

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Acquired\Payments\Gateway\Config\Basic;

/**
 * @class StatusUpdateProcessor
 *
 * Handles processing of "status_update" webhook notifications with integrity validation.
 */
class StatusUpdateProcessor extends AbstractProcessor
{
    /**
     * @param Basic $basicConfig
     * @param SerializerInterface $serializer
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderManagementInterface $orderManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Basic $basicConfig,
        SerializerInterface $serializer,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderManagementInterface $orderManagement,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
    )
    {
        parent::__construct($basicConfig, $serializer);
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
        $incrementId = $webhookBody['order_id'];

        $order = $this->getOrderByIncrementId($webhookBody['order_id']);
        if (!$order) {
            return $this->createResponse(__('Order #%1 not found.', $incrementId));
        }

        if ($webhookBody['status'] === self::STATUS_CANCELED) {
            return $this->cancelOrder($order, $incrementId);
        }

        return $this->createResponse(__('No action required for order #%1.', $incrementId));
    }

    /**
     * Cancels an order based on the provided order entity and increment ID.
     *
     * @param OrderInterface $order The order entity to be cancelled.
     * @param string $incrementId The increment ID of the order to provide context in the response.
     * @return array Associative array indicating the success status and message of the cancellation action.
     */
    private function cancelOrder(OrderInterface $order, string $incrementId): array
    {
        if (!$order->canCancel()) {
            return $this->createResponse(__('Order #%1 cannot be cancelled.', $incrementId));
        }

        try {
            $this->orderManagement->cancel($order->getEntityId());
            return $this->createResponse( __('Order #%1 cancelled successfully.', $incrementId), true);
        } catch (LocalizedException $e) {
            return $this->createResponse( __('Error cancelling order #%1: %2', $incrementId, $e->getMessage()));
        }
    }

    /**
     * Creates a standardized response array for the process outcome.
     *
     * @param string $message The message describing the outcome of the process.
     * @param bool $success Optional. Indicates whether the process was successful. Default is false.
     * @return array Associative array containing the success status and message.
     */
    private function createResponse(string $message, bool $success = false): array
    {
        return ['success' => $success, 'message' => $message];
    }

    /**
     * Retrieves an order by its increment ID.
     *
     * @param string $incrementId The increment ID of the order to retrieve.
     * @return OrderInterface|null Returns the order object if found, otherwise null.
     */
    private function getOrderByIncrementId(string $incrementId): ?OrderInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId)
            ->setPageSize(1)
            ->create();

        $orderList = $this->orderRepository->getList($searchCriteria)->getItems();
        return array_shift($orderList) ?: null;
    }
}
