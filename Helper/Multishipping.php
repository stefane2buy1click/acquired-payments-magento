<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Model\Session\Quote;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Helper\Context;

class Multishipping extends AbstractHelper
{

    /**
     * @var Quote
     */
    private $backendSessionQuote;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var State
     */
    private $state;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var int
     */
    private $quoteId;

    public function __construct(
        Quote $backendSessionQuote,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        StoreManagerInterface $storeManager,
        State $state,
        Context $context
    ) {
        parent::__construct($context);
        $this->backendSessionQuote = $backendSessionQuote;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->state = $state;
        $this->storeManager = $storeManager;
    }

    public function getCurrentStore()
    {
        return $this->storeManager->getStore();
    }

    public function getAreaCode()
    {
        try {
            return $this->state->getAreaCode();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function isAdmin()
    {
        $areaCode = $this->getAreaCode();

        return $areaCode == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
    }

    public function getQuote($quoteId = null): \Magento\Quote\Api\Data\CartInterface
    {
        // Admin area new order page
        if ($this->isAdmin())
            return $this->getBackendSessionQuote();

        // Front end checkout
        $quote = $this->getSessionQuote();

        // API Request
        if (empty($quote) || !is_numeric($quote->getGrandTotal())) {
            try {
                if ($quoteId)
                    $quote = $this->quoteRepository->get($quoteId);
                else if ($this->quoteId) {
                    $quote = $this->quoteRepository->get($this->quoteId);
                }
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return $quote;
    }

    private function getBackendSessionQuote()
    {
        return $this->backendSessionQuote->getQuote();
    }

    private function getSessionQuote()
    {
        return $this->checkoutSession->getQuote();
    }
}
