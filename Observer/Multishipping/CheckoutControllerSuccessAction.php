<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Observer\Multishipping;

use Acquired\Payments\Api\Data\MultishippingInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Acquired\Payments\Service\MultishippingService;
use Acquired\Payments\Api\MultishippingRepositoryInterface;
use Acquired\Payments\Ui\Method\PayByBankProvider;
use Acquired\Payments\Ui\Method\CardProvider;
use Acquired\Payments\Controller\Hosted\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\Http as HttpResponse;
use Acquired\Payments\Client\Gateway;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;

class CheckoutControllerSuccessAction implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @param CheckoutSession $checkoutSession
     * @param MultishippingService $multishippingService
     */
    public function __construct(
        private readonly CheckoutSession $checkoutSession,
        private readonly MultishippingService $multishippingService,
        private readonly MultishippingRepositoryInterface $multishippingRepository,
        private readonly Context $hostedContext,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ActionFlag $actionFlag,
        private readonly HttpResponse $httpResponse,
        private readonly Gateway $gateway,
        private readonly InvoiceSender $invoiceSender
    ) {
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $transactionId = $this->checkoutSession->getMultishippingTransactionId();
        $this->checkoutSession->unsMultishippingTransactionId();

        /* Update multishipping table status */
        if (!empty($transactionId)) {
            $multishippingItems = $this->multishippingService->getMultishippingByTransactionId($transactionId);

            foreach ($multishippingItems as $row) {
                $row->setStatus(MultishippingInterface::STATUS_SUCCESS);
                $row->save();
            }
        }

        $orderIds = $observer->getEvent()->getOrderIds();

        $customerId = null;
        $multishippingOrderId = null;
        $orders = [];
        $amount = 0;

        $orderRepository = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Sales\Api\OrderRepositoryInterface::class);

        // fetch orders by ids using order repository
        // check if there are any orders to process
        foreach ($orderIds as $orderId) {
            $order = $orderRepository->get($orderId);
            if($order->getPayment()->getMethod() == PayByBankProvider::CODE && $order->getState() == 'payment_review') {

                // check if multishipping is processed already
                $multishippingItem = $this->multishippingService->getMultishippingByReservedOrderId($order->getIncrementId());

                if($multishippingItem->getStatus() == MultishippingInterface::STATUS_SUCCESS) {
                    continue;
                }

                $multishippingItem->setOrderId($order->getId());
                $multishippingItem->save();

                if(!$multishippingOrderId) {
                    $multishippingOrderId = $order->getIncrementId() . "-ACQM";
                }
                if(!$customerId && $order->getCustomerId()) {
                    $customerId = $order->getCustomerId();
                }
                $orders[] = $order;
                $amount += $order->getGrandTotal();
            }

            if($order->getPayment()->getMethod() == CardProvider::CODE) {
                foreach ($order->getInvoiceCollection() as $invoice)
                {
                    if(!$invoice->getEmailSent()) {
                        $this->invoiceSender->send($invoice);
                        $invoice->setEmailSent(1);
                        $invoice->save();
                    }
                }
            }
        }

        if($amount && count($orders)) {
            $this->processHostedRedirect($multishippingOrderId, $amount, $orders, $customerId);
        }


    }

    protected function processHostedRedirect($orderId, $amount, $orders, $customerId) {
        $orderIds = [];
        foreach($orders as $order) {
            $orderIds[] = $order->getIncrementId();
        }
        $customData = [
            'custom1' => 'multishipping order',
            'custom2' => implode(",", $orderIds)
        ];

        if($customerId) {
            $customData['customer_id'] = $customerId;
        }



        $requestData = $this->hostedContext->hostedCheckoutBuilder->getData($orderId, $amount, $customData);
        $response = $this->gateway->getPaymentLinks()->generateLinkId($requestData);

        if ($response && $response['link_id']) {
            foreach($orders as $order) {
                $order->getPayment()->setLastTransId($response['link_id']);
                $order->getPayment()->save();

                $multishippingItem = $this->multishippingService->getMultishippingByReservedOrderId($order->getIncrementId());
                if($multishippingItem) {
                    $multishippingItem->setAcquiredTransactionId($response['link_id']);
                    $multishippingItem->setAcquiredSessionId($response['link_id']);
                    $multishippingItem->save();
                }
            }

            $redirectLink = $this->hostedContext->basicConfig->getRedirectUrl() . $response['link_id'];

            $this->httpResponse->setRedirect($redirectLink);
            $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
        }
    }
}