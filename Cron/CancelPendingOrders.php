<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Cron;

use Acquired\Payments\Ui\Method\PayByBankProvider;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Event\ManagerInterface;
use Psr\Log\LoggerInterface;

class CancelPendingOrders
{
    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly  SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly FilterBuilder $filterBuilder,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderManagementInterface $orderManagement,
        private readonly ManagerInterface $eventManager
    ) {
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $this->logger->debug('Cron job CancelPendingOrders started.');

        $offset = 24 * 3600;
        $orders = $this->getValidOrders($offset);

        foreach($orders as $order) {
            /** @var $order \Magento\Sales\Model\Order */
            if( time() - strtotime($order->getCreatedAt()) >= $offset ) {
                $this->logger->debug('Cron job CancelPendingOrders cancelling order ' . $order->getIncrementId());

                $order->registerCancellation('Order cancelled due to payment review timeout.');
                $this->eventManager->dispatch('order_cancel_after', ['order' => $order]);

                $this->orderRepository->save($order);
            } else {
                $this->logger->debug('Cron job CancelPendingOrders skipping order ' . strtotime($order->getCreatedAt()) - time() . " | " . $order->getIncrementId());
            }
        }

        $this->logger->debug('Cron job CancelPendingOrders executed.');

    }

    protected function getValidOrders($timeOffset = 86400)
    {

        $methodFilter = $this->filterBuilder->setField('extension_attribute_payment_method.method')
            ->setConditionType('eq')
            ->setValue(PayByBankProvider::CODE)
            ->create();

        $statusFilter = $this->filterBuilder->setField('status')
            ->setConditionType('eq')
            ->setValue('payment_review')
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters([$methodFilter])
            ->addFilters([$statusFilter])
            ->setPageSize(50)->setCurrentPage(1)->create();

        $ordersList = $this->orderRepository->getList($searchCriteria);

        $ordersList->getSelect()->where(
            new \Zend_Db_Expr('TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP, `created_at`)) >= ' . $timeOffset )
        );

        return $ordersList;
    }
}
