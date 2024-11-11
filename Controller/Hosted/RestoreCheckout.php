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

/**
 * @class RestoreCheckout
 *
 * Restores checkout based on a one time restore nonce
 */
class RestoreCheckout extends AbstractAction implements CsrfAwareActionInterface, HttpPostActionInterface
{

    /**
     * Executes the action to process hosted checkout restore of quote
     */
    public function execute()
    {
        try {
            $nonce = $this->getRequest()->getParam('nonce');

            $order = $this->getOrderFromNonce($nonce);

            // nonce validated, restore quote
            $this->hostedContext->checkoutSession->setLastOrderId($order->getId());
            $this->hostedContext->checkoutSession->setLastRealOrderId($order->getIncrementId());
            $this->hostedContext->checkoutSession->restoreQuote();

            $this->_redirect('checkout', ['_fragment' => 'payment']);
        } catch (Exception $e) {
            $this->hostedContext->logger->critical(__('Error handling Hosted: %1', $e->getMessage()), ['exception' => $e]);

            // add error message
            $this->messageManager->addErrorMessage(__('An error occured restoring checkout session'));

            // redirect to home page
            return $this->_redirect('');
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
