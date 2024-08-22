<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Controller\Hosted;

use Exception;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Acquired\Payments\Api\Data\MultishippingInterface;
use Acquired\Payments\Ui\Method\PayByBankProvider;

/**
 * @class RetryPayment
 *
 * Retries payment based on a one time payment nonce
 */
class RetryPayment extends AbstractAction implements CsrfAwareActionInterface, HttpPostActionInterface
{

    /**
     * Executes the action to process hosted checkout response
     */
    public function execute()
    {
        try {
            $nonce = $this->getRequest()->getParam('nonce');

            $order = $this->getOrderFromNonce($nonce);
            $response = $this->getRedirectResponse($order);

            if ($response && $response['link_id']) {
                $order->getPayment()->setLastTransId($response['link_id']);
                $order->getPayment()->save();
            }

            $resultRedirect = $this->resultRedirectFactory->create();
            $redirectLink = $this->hostedContext->basicConfig->getRedirectUrl() . $response['link_id'];
            $resultRedirect->setUrl($redirectLink);

            return $resultRedirect;
        } catch (Exception $e) {
            $this->hostedContext->logger->critical(__('Error handling Hosted: %1', $e->getMessage()), ['exception' => $e]);
            $this->messageManager->addErrorMessage(__('Payment could not be retried at this point.'));

            return $this->_redirect('');
        }
    }

    protected function getRedirectResponse($order) {

        $amount = $order->getPayment()->getAmountOrdered();
        $isMultishipping = $this->checkMultishipping($order);

        $customData = [];

        if($order->getCustomerId()) {
            $customData['customer_id'] = $order->getCustomerId();
        }

        $requestData = $isMultishipping ? $this->processMultishipping($order) : $this->hostedContext->hostedCheckoutBuilder->getData($order->getIncrementId(), $amount, $customData);
        $requestData['transaction']['order_id'] = $requestData['transaction']['order_id'] . "-ACQR-" . time();

        $response = $this->hostedContext->gateway->getPaymentLinks()->generateLinkId($requestData);

        return $response;
    }

    protected function checkMultishipping($order) {
        $multishippingItem = $this->hostedContext->multishippingService->getMultishippingByReservedOrderId($order->getIncrementId());
        return $multishippingItem !== null && $multishippingItem->getId();
    }

    protected function processMultishipping($order) {
        $customerId = null;
        $multishippingOrderId = null;
        $orders = [];
        $orderIds = [];
        $amount = 0;

        $multishippingItem = $this->hostedContext->multishippingService->getMultishippingByReservedOrderId($order->getIncrementId());
        $multishippingItems = $this->hostedContext->multishippingService->getMultishippingByTransactionId($multishippingItem->getAcquiredTransactionId());

        $incrementIds = [];
        foreach($multishippingItems as $mi) {
            $incrementIds[] = $mi->getQuoteReservedId();
        }

        $orderCollection = $this->hostedContext->orderCollectionFactory->create();
        $orderCollection->addFieldToSelect("*");
        $orderCollection->addFieldToFilter('increment_id', ['in' => $incrementIds]);

        foreach($orderCollection->getItems() as $order) {
            if($order->getPayment()->getMethod() == PayByBankProvider::CODE && $order->getState() == 'payment_review') {

                // check if multishipping is processed already
                $multishippingItem = $this->hostedContext->multishippingService->getMultishippingByReservedOrderId($order->getIncrementId());

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
        }

        foreach($orders as $order) {
            $orderIds[] = $order->getIncrementId();
        }
        $customData = [
            'custom1' => 'multishipping order',
            'custom2' => implode(",", $orderIds)
        ];

        if($order->getCustomerId()) {
            $customData['customer_id'] = $order->getCustomerId();
        }

        $requestData = $this->hostedContext->hostedCheckoutBuilder->getData($multishippingOrderId, $amount, $customData);

        return $requestData;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
