<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Controller\Hosted;

use Exception;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Acquired\Payments\Service\MultishippingService;
use Acquired\Payments\Controller\Hosted\Context as HostedContext;

/**
 * @class Response
 *
 * Manages incoming hosted payment responses.
 */
class Response extends AbstractAction implements CsrfAwareActionInterface, HttpPostActionInterface
{

    const PARAMS = [
        'status', // The final status of the payment.
        'transaction_id', // UUID assigned by Acquired.com.
        'order_id', // The order ID value that was passed in the payment-link request.
        'order_active', // Whether the order ID is still active or not, and whether payment can be reattempted by the same payment link.
        'timestamp', // Exact date when response was posted to the redirect URL in UNIX timestamp format.
        'hash' // A calculated hash value which can be used to verify the integrity of the data.
    ];

    /**
     * Constructs a valid hosted redirect response or throws an exception if invalid data is received
     *
     * @param array $postData
     * @return void
     */
    protected function constructResponse($postData)
    {
        $appKey = $this->hostedContext->basicConfig->getApiSecret();

        foreach (self::PARAMS as $param) {
            if (!isset($postData[$param])) {
                throw new Exception("Missing required parameter: $param");
            }
        }

        $response = [
            'status' => $postData['status'],
            'transaction_id' => $postData['transaction_id'],
            'order_id' => $postData['order_id'],
            'order_active' => $postData['order_active'],
            'timestamp' => $postData['timestamp'],
            'hash' => $postData['hash']
        ];

        $hash = $this->generateHashFromResponse($response, $appKey);

        if ($hash !== $response['hash']) {
            throw new Exception('Hash verification failed');
        }

        return $response;
    }

    /**
     * Generates hash to validate the data integrity
     * For more see: https://docs.acquired.com/docs/set-up-integrate#merchant-handles-the-customer-redirect
     *
     * @param array $response
     * @param string $appKey
     * @return string
     */
    private function generateHashFromResponse(array $response, string $appKey) : string {
        // Concatenate status.transaction_id.order_id.timestamp
        $hashBody = implode('', [$response['status'], $response['transaction_id'], $response['order_id'], $response['timestamp']]);
        // hash the output using sha256
        $hashBody = hash('sha256', $hashBody);
        // append app_key
        $hashBody = $hashBody . $appKey;
        // hash the output using sha256 again
        $hashBody = hash('sha256', $hashBody);

        return $hashBody;
    }

    /**
     * Executes the action to process hosted checkout response
     */
    public function execute()
    {
        try {
            $postData = $this->getRequest()->getPostValue();
            $response = $this->constructResponse($postData);

            $this->hostedContext->logger->debug(__('Hosted Response'), ['response' => $postData]);
            $this->hostedContext->logger->critical(json_encode($response));

            $this->hostedContext->coreRegistry->register('acquired_hosted_response', $response);

            $incrementId = $response['order_id'];
            if (strpos($incrementId, HostedContext::HOSTED_ORDER_ID_RETRY_IDENTIFIER)) {
                // replace everything starting from the order retry identifier with ''
                $incrementId = substr($incrementId, 0, strpos($incrementId, HostedContext::HOSTED_ORDER_ID_RETRY_IDENTIFIER));
            }

            $multishippingFlag = false;
            // if order ends with multishipping suffix then it is multishipping
            // remove the suffix and get the order id
            if (strpos($incrementId, MultishippingService::MULTISHIPPING_ORDER_ID_SUFFIX) !== false) {
                $incrementId = substr($incrementId, 0, strpos($incrementId, MultishippingService::MULTISHIPPING_ORDER_ID_SUFFIX));
                $multishippingFlag = true;
            }

            $order = $this->hostedContext->orderFactory->create()->loadByIncrementId($incrementId);
            if (!$order->getId()) {
                throw new NotFoundException(__('Order not found: %1', $incrementId));
            }

            // sets http context value to authorized to bypass display issue with header
            if ($order->getCustomerId()) {
                $this->hostedContext->httpContext->setValue(\Magento\Customer\Model\Context::CONTEXT_AUTH, true, false);
            }

            if (
                in_array($response['status'], ['success', 'settled', 'executed'])
            ) {
                // check if multi shipping
                if ($multishippingFlag) {
                    $multishippingItem = $this->hostedContext->multishippingService->getMultishippingByReservedOrderId($order->getIncrementId());
                    $multishippingItems = $this->hostedContext->multishippingService->getMultishippingByTransactionId($multishippingItem->getAcquiredTransactionId());

                    $this->validateMultishippingInformation($multishippingFlag, $order, $multishippingItem, $multishippingItems);

                    foreach ($multishippingItems as $multishippingItem) {
                        $multishippingItem->setStatus("success");
                        $multishippingItem->save();
                    }
                    $this->hostedContext->checkoutSession->setMultishippingTransactionId($order->getPayment()->getLastTransId());

                    return $this->_redirect('multishipping/checkout/success/');
                }


                $this->hostedContext->checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
                $this->hostedContext->checkoutSession->setLastQuoteId($order->getQuoteId());
                $this->hostedContext->checkoutSession->setLastOrderId($order->getId());
                $this->hostedContext->checkoutSession->setLastRealOrderId($order->getIncrementId());
                $this->hostedContext->checkoutSession->setLastOrderStatus($order->getStatus());

                return $this->_redirect('checkout/onepage/success');
            } else {
                $this->hostedContext->coreRegistry->register('acquired_order_id', $order->getIncrementId());
                $this->generateNonce($order);
            }

            $resultPage = $this->hostedContext->pageFactory->create();
            return $resultPage;
        } catch (NotFoundException | NoSuchEntityException $e) {
            $this->hostedContext->logger->critical(__('Hosted response order not found: %1', $e->getMessage()), ['exception' => $e]);
            $this->messageManager->addErrorMessage('Order not found');

            return $this->_redirect('');
        } catch (Exception $e) {
            $this->hostedContext->logger->critical(__('Error handling Hosted: %1', $e->getMessage()), ['exception' => $e]);
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('');
        }
    }

    protected function validateMultishippingInformation($multishippingFlag, $order, $multishippingItem, $multishippingItems)
    {
        if (!$multishippingFlag) {
            throw new \Exception("Not multishipping checkout flow");
        }

        $quote = $order->getQuote();

        if ($quote && !$quote->getIsMultiShipping()) {
            throw new \Exception("Invalid order type");
        }

        if (!count($multishippingItems)) {
            throw new \Exception("Could not find linked multishipping orders");
        }
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
