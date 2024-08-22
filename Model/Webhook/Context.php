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

namespace Acquired\Payments\Model\Webhook;

use InvalidArgumentException;
use Acquired\Payments\Model\Webhook\Processor\CompositeStatusUpdateProcessorFactory;
use Acquired\Payments\Model\Webhook\Processor\UnsupportedTypeProcessorFactory;
use Exception;
use Magento\Framework\Serialize\SerializerInterface;
use Acquired\Payments\Exception\Webhook\WebhookVersionException;
use Acquired\Payments\Exception\Webhook\WebhookIntegrityException;
use Acquired\Payments\Gateway\Config\Basic;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Acquired\Payments\Service\MultishippingService;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

/**
 * @class Context
 *
 * Webhook actions context
 */
class Context
{


    public function __construct(
        public readonly Basic $basicConfig,
        public readonly SerializerInterface $serializer,
        public readonly OrderRepositoryInterface $orderRepository,
        public readonly OrderManagementInterface $orderManagement,
        public readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        public readonly InvoiceService $invoiceService,
        public readonly Transaction $transaction,
        public readonly InvoiceSender $invoiceSender,
        public readonly OrderSender $orderSender,
        public readonly MultishippingService $multishippingService,
        public readonly OrderCollectionFactory $orderCollectionFactory
    ) { }

}