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

namespace Acquired\Payments\Block\Applepay;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Framework\Escaper;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Locale\Resolver;

class MinicartButton extends Template implements ShortcutInterface
{
    protected $_template = 'Acquired_Payments::applepay/applepay-minicart-button.phtml';

    const CONFIG_APPLE_PAY_BUTTON_CONFIG = 'payment/acquired_express_payments/applepay_style';
    const APPLE_PAY_BUTTON_STYLE = [
        'black' => '',
        'white' => 'apple-pay-button-white-with-text',
        'white-outline' => 'apple-pay-button-white-with-line-with-text'
    ];

    /**
     * @param Context $context
     * @param Escaper $escaper
     * @param Session $checkoutSession
     * @param Resolver $localeResolver
     * @param array $data
     */
    public function __construct(
        private readonly Context $context,
        private readonly Escaper $escaper,
        private readonly Session $checkoutSession,
        private readonly Resolver $localeResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Retrieves the escaper instance for sanitizing output.
     *
     * @return \Magento\Framework\Escaper
     */
    public function getEscaper()
    {
        return $this->escaper;
    }

    /**
     * Retrieves the style class for the Apple Pay button based on configuration.
     *
     * @return string
     */
    public function getButtonStyle()
    {
        $buttonStyle = $this->_scopeConfig->getValue(self::CONFIG_APPLE_PAY_BUTTON_CONFIG, ScopeInterface::SCOPE_STORE);

        if (isset(self::APPLE_PAY_BUTTON_STYLE[$buttonStyle])) {
            return self::APPLE_PAY_BUTTON_STYLE[$buttonStyle];
        }

        return self::APPLE_PAY_BUTTON_STYLE['black'] ?? '';
    }

    /**
     * Retrieves the alias identifier for the Apple Pay mini-cart button.
     *
     * @return string
     */
    public function getAlias(): string
    {
        return 'acquired.payments.applepay.mini-cart';
    }

    /**
     * Retrieves the base grand total of the current quote.
     *
     * @return float|null
     */
    public function getBaseGrandTotal(): ?float
    {
        return (float)$this->checkoutSession->getQuote()->getBaseGrandTotal();
    }

    /**
     * Retrieves the store's name.
     *
     * @return string
     */
    public function getStoreName(): string
    {
        return $this->_storeManager->getStore()->getName();
    }

    /**
     * Retrieves the default country of the store.
     *
     * @return string
     */
    public function getStoreCountry(): string
    {
        return $this->_scopeConfig->getValue('general/country/default', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieves the store's current currency code.
     *
     * @return string
     */
    public function getStoreCurrency(): string
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Retrieves the locale for the Apple Pay button.
     * 
     * @return string
     */
    public function getButtonLocale(): string
    {
        $locale = $this->localeResolver->getLocale();

        if (empty($locale)) {
            return 'en';
        }

        return strtolower(strstr($locale, '_', true));
    }

    /**
     * Generates the CSS classes for the Apple Pay button.
     *
     * @return string
     */
    public function getButtonClasses(): string
    {
        $classes = [];
        $classes[] = 'acquired-payments-applepay-minicart-button';
        $classes[] = 'apple-pay-button';
        $classes[] = 'apple-pay-button-text-buy';
        $classes[] = $this->getButtonStyle();

        return implode(' ', $classes);
    }

    /**
     * Retrieves the supported payment networks for Apple Pay.
     *
     * @return array
     */
    public function getSupportedNetworks(): array
    {
        return ['visa', 'masterCard', 'amex', 'discover'];
    }

    /**
     * Determines if the Apple Pay button should be rendered.
     *
     * @return bool
     */
    protected function shouldRender()
    {
        return (bool)$this->getIsCart();
    }

    /**
     * Renders the HTML for the Apple Pay button if rendering is allowed.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->shouldRender())
            return '';

        return parent::_toHtml();
    }

}