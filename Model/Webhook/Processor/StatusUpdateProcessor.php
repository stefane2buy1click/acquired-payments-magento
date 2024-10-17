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

namespace Acquired\Payments\Model\Webhook\Processor;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order;
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
        private readonly InvoiceService $invoiceService,
        private readonly Transaction $transaction,
        private readonly InvoiceSender $invoiceSender
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

        if(strpos($incrementId, 'R-')) {
            // replace everything starting from 'R-' with ''
            $incrementId = substr($incrementId, 0, strpos($incrementId, 'R-'));
        }

        $order = $this->getOrderByIncrementId($incrementId);
        if (!$order) {
            return $this->createResponse(__('Order #%1 not found.', $incrementId));
        }

        if ($webhookBody['status'] === self::STATUS_CANCELED) {
            return $this->cancelOrder($order, $incrementId);
        }

        if (
            ($webhookBody['status'] === self::STATUS_EXECUTED || $webhookBody['status'] === self::STATUS_SETTLED || $webhookBody['status'] === self::STATUS_SUCCESS)
            && $this->canInvoiceOrder($order)
        ) {
            $order->setState(Order::STATE_PROCESSING);
            return $this->invoiceOrder($order, $incrementId, $webhookBody['transaction_id']);
        }

        return $this->createResponse(__('No action required for order #%1.', $incrementId));
    }

    private function canInvoiceOrder(OrderInterface $order): bool
    {
        // check payment method and status
        $payment = $order->getPayment();
        if ($payment->getMethod() !== 'acquired_pay_by_bank') {
            return false;
        }

        if ($order->getState() !== 'payment_review') {
            return false;
        }

        return true;
    }

    /**
     * Creates an invoice for the provided order entity and increment ID.
     *
     * @param OrderInterface $order The order entity to be cancelled.
     * @param string $incrementId The increment ID of the order to provide context in the response.
     * @param string $transactionId New and latest Acquired transaction Id.
     * @return array Associative array indicating the success status and message of the cancellation action.
     */
    private function invoiceOrder(OrderInterface $order, string $incrementId, string $transactionId): array
    {
        if (!$order->canInvoice()) {
            return $this->createResponse(__('Order #%1 cannot be invoiced.', $incrementId));
        }

        try {

            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $order->getPayment();
            $payment->setLastTransId($transactionId);

            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
            $invoice->register();
            $invoice->save();

            $transactionSave =
                $this->transaction
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->addObject($payment);
            $transactionSave->save();

            $this->invoiceSender->send($invoice);
            $order->addCommentToStatusHistory(
                __('Notified customer about invoice creation #%1.', $invoice->getId())
            )->setIsCustomerNotified(true)->save();

            return $this->createResponse( __('Order #%1 invoiced successfully.', $incrementId), true);
        } catch (LocalizedException $e) {
            return $this->createResponse( __('Error invoicing order #%1: %2', $incrementId, $e->getMessage()));
        }
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
