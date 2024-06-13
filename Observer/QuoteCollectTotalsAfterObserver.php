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

use Psr\Log\LoggerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Acquired\Payments\Api\SessionInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\State;
use Magento\Backend\Model\Session\Quote as BackendModelSession;
use Acquired\Payments\Ui\Method\CardProvider;

class QuoteCollectTotalsAfterObserver implements ObserverInterface
{

    public function __construct(
        protected SessionInterface $acquiredSession,
        protected CheckoutSession $checkoutSession,
        protected BackendModelSession $backendQuoteSession,
        protected State $state,
        protected LoggerInterface $logger
    ) {}

    /**
     * Flag to prevent infinite loop
     * @var bool
     */
    private $updatingSessionFlag = false;

    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $nonce = $this->getCheckoutSession()->getAcquiredSessionNonce();
        $sessionId = $this->getCheckoutSession()->getAcquiredSessionId();
        if($nonce && $sessionId && $quote->getPayment()->getMethod() == CardProvider::CODE && !$this->updatingSessionFlag) {
            $this->updatingSessionFlag = true;
            try {
                $this->acquiredSession->update($nonce, $sessionId);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
            $this->updatingSessionFlag = false;
        }
    }

    protected function getCheckoutSession() {
        return $this->state->getAreaCode() === 'adminhtml' ? $this->backendQuoteSession : $this->checkoutSession;
    }
}