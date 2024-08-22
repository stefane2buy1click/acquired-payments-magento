<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Controller\Threedsecure;

use Exception;
use Psr\Log\LoggerInterface;
use Acquired\Payments\Model\TdsResponseHandler;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;

/**
 * @class Response
 *
 * Manages incoming 3-D Secure responses.
 * Coordinates with the TdsResponseHandler model to process and present results to the user.
 */
class Response extends Action implements CsrfAwareActionInterface
{

    /**
     * Initializes the controller with necessary dependencies.
     *
     * @param Context $context Magento context object.
     * @param PageFactory $pageFactory Factory to create page result.
     * @param TdsResponseHandler $tdsResponseHandler Handler for processing 3-D Secure responses.
     */
    public function __construct(
        Context $context,
        private readonly PageFactory $pageFactory,
        private readonly TdsResponseHandler $tdsResponseHandler,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    /**
     * Executes the action to process the 3-D Secure response.
     *
     * @return ResultInterface The result page with the 3-D Secure response data.
     */
    public function execute(): ResultInterface
    {
        try {
            $postData = $this->getRequest()->getParams();
            $response = $this->serializer->serialize($this->tdsResponseHandler->processResponse($postData));

            $this->logger->debug(__('3DS Response'), ['response' => $response]);

            $resultPage = $this->pageFactory->create();
            $resultPage->getLayout()->getUpdate()->removeHandle('default');
            $block = $resultPage->getLayout()->getBlock('acquired_tds_response');
            if ($block) {
                $block->setData('response_data', $response);
                $this->getResponse()->appendBody($block->toHtml());
            }

            header_remove("Set-Cookie");
            return $resultPage;
        } catch (Exception $e) {
            $this->logger->critical(__('Error handling 3DS: %1', $e->getMessage()), ['exception' => $e]);
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
