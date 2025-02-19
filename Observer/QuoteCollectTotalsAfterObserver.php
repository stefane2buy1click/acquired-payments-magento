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

namespace Acquired\Payments\Observer;

use Acquired\Payments\Api\Data\PaymentIntentInterface;
use Acquired\Payments\Api\SessionInterface;
use Acquired\Payments\Model\Payment\IntentFactory as PaymentIntentFactory;
use Acquired\Payments\Model\ResourceModel\Payment\Intent as PaymentIntentResource;
use Acquired\Payments\Ui\Method\CardProvider;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class QuoteCollectTotalsAfterObserver implements ObserverInterface
{
    /**
     * Flag to prevent infinite loop
     * @var bool
     */
    private $updatingSessionFlag = false;

    public function __construct(
        protected SessionInterface $acquiredSession,
        protected LoggerInterface $logger,
        protected PaymentIntentFactory $paymentIntentFactory,
        protected PaymentIntentResource $paymentIntentResource
    ) {}

    public function execute(Observer $observer)
    {
        if ($this->updatingSessionFlag) {
            return;
        }

        $quote = $observer->getEvent()->getQuote();

        /** @var PaymentIntentInterface $paymentIntent */
        $paymentIntent = $this->paymentIntentFactory->create();
        $this->paymentIntentResource->load($paymentIntent, $quote->getId(), 'quote_id');

        $nonce = $paymentIntent->getNonce();
        $sessionId = $paymentIntent->getSessionId();

        if ($nonce && $sessionId && $quote->getPayment()->getMethod() == CardProvider::CODE) {
            $this->updatingSessionFlag = true;
            try {
                $this->acquiredSession->update($nonce, $sessionId);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
            $this->updatingSessionFlag = false;
        }
    }
}
