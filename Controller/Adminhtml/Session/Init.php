<?php

declare(strict_types=1);

/**
 * Acquired.com Payments Integration for Magento2
 *
 * Copyright (c) 2024 Acquired Limited (https://acquired.com/)
 *
 * This file is open source under the MIT license.
 * Please see LICENSE file for more details.
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

class Init extends Action implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var SessionInterface
     */
    protected $acquiredSession;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param SessionInterface $acquiredSession
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

        $result = $this->acquiredSession->get(
            $this->getRequest()->getParam('nonce'),
            $this->getRequest()->getParam('custom_data')
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
