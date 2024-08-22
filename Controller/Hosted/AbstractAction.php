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
use Psr\Log\LoggerInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Acquired\Payments\Controller\Hosted\Context as HostedContext;

/**
 * @class Response
 *
 * Restores checkout based on a one time restore nonce
 */
abstract class AbstractAction extends Action implements CsrfAwareActionInterface, HttpPostActionInterface
{

    /**
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param Registry $coreRegistry
     * @param OrderInterfaceFactory $orderFactory
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Context $context,
        public readonly HostedContext $hostedContext
    ) {
        parent::__construct($context, $hostedContext);
    }

    protected function getNonceData($nonce) {
        $decrypted = $this->hostedContext->encryptor->decrypt($nonce);
        $nonceParts = explode("::", $decrypted);

        if (count($nonceParts) !== 3) {
            throw new Exception("Invalid nonce");
        }

        $orderId = $nonceParts[0];
        $transactionId = $nonceParts[1];
        $salt = $nonceParts[2];

        return [
            'orderId' => $orderId,
            'transactionId' => $transactionId,
            'salt' => $salt
        ];
    }

    protected function getOrderFromNonce()
    {
        $nonce = $this->getRequest()->getParam('nonce');

        $decrypted = $this->hostedContext->encryptor->decrypt($nonce);
        $nonceParts = explode("::", $decrypted);

        if (count($nonceParts) !== 3) {
            throw new Exception("Invalid nonce");
        }

        $orderId = $nonceParts[0];
        $transactionId = $nonceParts[1];
        $salt = $nonceParts[2];

        $order = $this->hostedContext->orderFactory->create()->loadByIncrementId($orderId);

        if (!$order->getId()) {
            throw new Exception('Order not found');
        }

        $payment = $order->getPayment();
        $additionalData = $payment->getAdditionalInformation();
        $paymentNonceSalt = $additionalData['acquired_nonce_salt'];
        $computedNonce = $orderId . "::" . $order->getPayment()->getLastTransId() . "::" . $paymentNonceSalt;

        if ($decrypted !== $computedNonce) {
            throw new Exception('Nonce mismatch');
        }

        // nonce is valid so we consume it
        $this->generateNonce($order);

        return $order;
    }

    protected function generateNonce($order)
    {
        $payment = $order->getPayment();
        $additionalData = $payment->getAdditionalInformation();
        $paymentNonceSaltNew = bin2hex(random_bytes(16));
        $additionalData['acquired_nonce_salt'] = $paymentNonceSaltNew;
        $payment->setAdditionalInformation($additionalData);
        $payment->save();
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
