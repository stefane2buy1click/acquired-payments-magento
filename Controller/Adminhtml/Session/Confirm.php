<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Controller\Adminhtml\Session;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Acquired\Payments\Api\SessionInterface;

class Confirm extends Action implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Acquired\Payments\Api\SessionInterface
     */
    protected $acquiredSession;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param acquiredSession $acquiredSession
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        SessionInterface $acquiredSession
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->acquiredSession = $acquiredSession;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return void
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $result = $this->acquiredSession->prepareForPurchase(
            $this->getRequest()->getParam('nonce'),
        );

        return $resultJson->setData($result);
    }

    protected function _isAllowed()
    {
        return true;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
