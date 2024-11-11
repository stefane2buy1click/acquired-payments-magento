<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Model\Api;

use Exception;
use Psr\Log\LoggerInterface;
use Acquired\Payments\Api\Data\SessionDataInterface;
use Acquired\Payments\Api\SessionInterface;
use Acquired\Payments\Client\Gateway;
use Acquired\Payments\Model\Api\Response\SessionIdFactory;
use Acquired\Payments\Service\PaymentSessionDataInterface;
use Acquired\Payments\Exception\Api\SessionException;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Framework\App\State;
use Magento\Backend\Model\Session\Quote as BackendModelSession;

class AcquiredSession implements SessionInterface
{

    /**
     * @param SessionIdFactory $sessionIdFactory
     * @param PaymentSessionDataInterface $getPaymentSessionData
     * @param Gateway $gateway
     */
    public function __construct(
        private readonly Response\SessionIdFactory $sessionIdFactory,
        private readonly PaymentSessionDataInterface $getPaymentSessionData,
        private readonly Gateway $gateway,
        private readonly LoggerInterface $logger,
        private readonly CheckoutSession $checkoutSession,
        private readonly BackendModelSession $backendQuoteSession,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly State $state
    ) {
    }

    protected function getCheckoutSession()
    {
        return $this->state->getAreaCode() === 'adminhtml' ? $this->backendQuoteSession : $this->checkoutSession;
    }

    /**
     * Create a fingerprint for the payment data
     *
     * @param array $paymentData
     * @return string
     */
    protected function createFingerprint(array $paymentData): string
    {
        // remove order_id from payment data as it is nonce specific, use quote id instead for fingerprint
        unset($paymentData['transaction']['order_id']);
        $paymentData['transaction']['order_id'] = $this->getQuote()->getId();

        // remove transaction amount to allow updates to the quote without recreating a new session
        unset($paymentData['transaction']['amount']);

        // remove address data from fingerprint
        unset($paymentData['customer']['billing']);
        unset($paymentData['customer']['shipping']);

        return md5(trim(json_encode($paymentData)));
    }

    /**
     * @param array $payload
     * @return SessionDataInterface
     * @throws SessionException
     */
    protected function createNewSession(array $payload, string $nonce) : SessionDataInterface
    {
        $acquiredSession = $this->sessionIdFactory->create();
        $quote = $this->getQuote();

        if(!$quote->getReservedOrderId() || $nonce !== $this->getCheckoutSession()->getAcquiredSessionNonce()) {
            $quote->setReservedOrderId(null);
            $quote->reserveOrderId();
            $this->cartRepository->save($quote);
        }

        try {
            $response = $this->gateway->getComponent()->create(
                $payload
            );

            $acquiredSession->setSessionId($response['session_id']);
            $this->getCheckoutSession()->setAcquiredSessionId($response['session_id']);
            $this->getCheckoutSession()->setAcquiredSessionFingerPrint($this->createFingerprint($payload));
            $this->getCheckoutSession()->setAcquiredSessionNonce($nonce);
        } catch (Exception $e) {
            $this->logger->critical(
                __('Create Acquired session failed: %1', $e->getMessage()),
                ['exception' => $e]
            );

            throw new SessionException(__('Create Acquired session failed!'));
        }
        return $acquiredSession;
    }

    /**
     * @param string $sessionId
     * @param array $payload
     * @return SessionDataInterface
     * @throws SessionException
     */
    protected function updateSession(string $sessionId, array $payload, string $nonce) : SessionDataInterface
    {
        $acquiredSession = $this->sessionIdFactory->create();
        $quote = $this->getQuote();

        if(!$quote->getReservedOrderId() || $nonce !== $this->getCheckoutSession()->getAcquiredSessionNonce()) {
            $quote->setReservedOrderId(null);
            $quote->reserveOrderId();
            $this->cartRepository->save($quote);
        }

        try {
            $response = $this->gateway->getComponent()->update(
                $sessionId,
                $payload
            );
            $acquiredSession->setSessionId($response['session_id']);
            $this->getCheckoutSession()->setAcquiredSessionId($response['session_id']);
            $this->getCheckoutSession()->setAcquiredSessionFingerPrint($this->createFingerprint($payload));
            $this->getCheckoutSession()->setAcquiredSessionNonce($nonce);
        } catch (Exception $e) {
            $this->logger->critical(
                __('Update Acquired session failed: %1', $e->getMessage()),
                ['exception' => $e]
            );

            throw new SessionException(__('Update Acquired session failed!'));
        }

        return $acquiredSession;
    }

    /**
     * @param string nonce
     * @param mixed $customData
     * @return SessionDataInterface
     * @throws SessionException
     */
    public function get(string $nonce, array $customData = null): SessionDataInterface
    {
        $customData = $customData ?? [];
        $orderId = $this->getQuote()->getId() . "_" . $nonce;
        $paymentData = $this->getPaymentSessionData->execute($orderId, $customData);

        // try to load session ID from PHP session
        $sessionId = $this->getCheckoutSession()->getAcquiredSessionId();
        if(!$sessionId) {
            // if no session ID, create new session
            return $this->createNewSession($paymentData, $nonce);
        }

        if ($sessionId && $this->validateFingerprint($paymentData) && $nonce === $this->getCheckoutSession()->getAcquiredSessionNonce()) {
            $acquiredSession = $this->sessionIdFactory->create();
            $acquiredSession->setSessionId($sessionId);
            return $acquiredSession;
        }

        // if fingerprint or nonce is different, create new session, use the new nonce to generate a new order id
        return $this->createNewSession($paymentData, $nonce);
    }

    /**
     * @param string $sessionId
     * @param mixed $customData
     * @return SessionDataInterface
     * @throws SessionException
     */
    public function update(string $nonce, string $sessionId, array $customData = null): SessionDataInterface
    {
        $customData = $customData ?? [];
        $orderId = $this->getQuote()->getId() . "_" . $nonce;
        $paymentData = $this->getPaymentSessionData->execute($orderId, $customData);

        // try to load session ID from PHP session and compare fingerprints
        $sessionId = $this->getCheckoutSession()->getAcquiredSessionId();

        // if fingerprint matches we update the session with new transaction id, nonce doesn't matter
        if ($sessionId && $this->validateFingerprint($paymentData)) {
            return $this->updateSession($sessionId, $paymentData, $nonce);
        }

        // otherwise we throw an error as nonce changed or the payload fingerprint is invalid
        throw new SessionException(__('Session ID does not match the current session'));
    }

    /**
     * Prepare the session for purchase, consuming the nonce and incrementing the order id
     *
     * @param string $nonce
     * @throws SessionException
     */
    public function prepareForPurchase(string $nonce) : void
    {
        if($nonce !== $this->getCheckoutSession()->getAcquiredSessionNonce()) {
            throw new SessionException(__('Nonce does not match the current session!'));
        }

        // this nonce is no longer valid
        $this->getCheckoutSession()->setAcquiredSessionNonce(null);

        $quote = $this->getQuote();
        if(!$quote->getReservedOrderId()) {
            $quote->setReservedOrderId(null);
            $quote->reserveOrderId();
            $this->cartRepository->save($quote);
        }
        $paymentData = $this->getPaymentSessionData->execute($quote->getReservedOrderId());

        try {
            $this->gateway->getComponent()->update(
                $this->getCheckoutSession()->getAcquiredSessionId(),
                $paymentData
            );
        } catch (Exception $e) {
            $this->logger->critical(
                __('Update Acquired session failed: %1', $e->getMessage()),
                ['exception' => $e]
            );

            throw new SessionException(__('Update Acquired session failed!'));
        }
    }

    /**
     * Get the current quote
     *
     * @return Quote
     */
    protected function getQuote() : Quote
    {
        return $this->getCheckoutSession()->getQuote();
    }

    /**
     * Validate the fingerprint
     *
     * @param array $paymentData
     * @return bool
     */
    protected function validateFingerprint($paymentData) : bool
    {
        // create fingerprint
        $payloadFingerprint = $this->createFingerprint($paymentData);
        // compare fingerprint with payment session data
        $fingerPrintId = $this->getCheckoutSession()->getAcquiredSessionFingerPrint();
        return $fingerPrintId === $payloadFingerprint;
    }
}
