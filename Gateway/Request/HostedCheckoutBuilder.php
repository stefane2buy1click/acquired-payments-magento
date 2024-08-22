<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Gateway\Request;


use Exception;
use Psr\Log\LoggerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\UrlInterface;
use Acquired\Payments\Exception\Command\BuilderException;
use Acquired\Payments\Gateway\Config\Hosted\Config as HostedConfig;
use Magento\Store\Model\StoreManagerInterface;
use Acquired\Payments\Api\AcquiredCustomerRepositoryInterface;
use Acquired\Payments\Model\Api\CreateAcquiredCustomer;

class HostedCheckoutBuilder implements BuilderInterface
{

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly StoreManagerInterface $storeManager,
        private readonly UrlInterface $urlBuilder,
        private readonly HostedConfig $hostedConfig,
        private readonly CreateAcquiredCustomer $createAcquiredCustomer
    ) {
    }

    /**
     * @param array $buildSubject
     * @return array
     * @throws BuilderException
     */
    public function build(array $buildSubject): array
    {
        try {
            $payment = SubjectReader::readPayment($buildSubject)->getPayment();
            $order = $payment->getOrder();
            $amount = (float)SubjectReader::readAmount($buildSubject);

            if ($order->getQuote() && $order->getQuote()->getIsMultiShipping()) {
                $order->setMultishippingAcquiredTransactionId('M-' . $order->getQuoteId());
            }

            $customData = [];
            if ($order->getCustomerId()) {
                $customData['customer_id'] = $order->getCustomerId();
            }

            if (strpos($this->urlBuilder->getUrl($this->hostedConfig->getRedirectUrl()), 'https://') === false) {
                throw new BuilderException(__('Redirect URL must be HTTPS: %1', $this->urlBuilder->getUrl($this->hostedConfig->getRedirectUrl())));
            }
            if (strpos($this->urlBuilder->getUrl($this->hostedConfig->getWebhookUrl()), 'https://') === false) {
                throw new BuilderException(__('Webhook URL must be HTTPS: %1', $this->urlBuilder->getUrl($this->hostedConfig->getWebhookUrl())));
            }

            return $this->getData($order->getIncrementId(), $amount, $customData);
        } catch (Exception $e) {
            $message = __('Authorize build failed: %1', $e->getMessage());
            $this->logger->critical($message, ['exception' => $e]);

            throw new BuilderException($message);
        }
    }

    public function getData($orderId, $amount, $customData = [])
    {
        $payload = [
            'transaction' => [
                'order_id' => $orderId,
                'amount' => $amount,
                'currency' => strtolower($this->storeManager->getStore()->getCurrentCurrencyCode()),
                'capture' => true,
            ],
            'redirect_url' => $this->urlBuilder->getUrl($this->hostedConfig->getRedirectUrl()),
            'webhook_url' => $this->urlBuilder->getUrl($this->hostedConfig->getWebhookUrl()),
            'payment' => [
                'reference' => $orderId
            ],
            'expires_in' => 3600
        ];

        if($this->hostedConfig->isBankOnly()) {
            $payload['payment_methods'] = ['pay_by_bank'];
        }

        if (isset($customData, $customData['custom1'])) {
            $payload['transaction']['custom1'] = $customData['custom1'];
        }
        if (isset($customData, $customData['custom2'])) {
            $payload['transaction']['custom2'] = $customData['custom2'];
        }
        if (isset($customData, $customData['customer_id'])) {
            $acquiredCustomer = $this->createAcquiredCustomer->execute($customData['customer_id']);
            if ($acquiredCustomer && isset($acquiredCustomer['customer_id'])) {
                $payload['customer'] = ['customer_id' => $acquiredCustomer['customer_id']];
            }
        }

        return $payload;
    }
}
