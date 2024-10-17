<?php

/**
 *
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 *
 *
 */

namespace Acquired\Payments\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @class Basic
 *
 * Handles basic configuration settings for the Acquired Limited Payment module.
 */
class Basic
{
    /**
     * Basic Configuration path prefix
     */
    protected const CONFIG_PATH = 'payment/acquired/configuration/';

    // Definition of basic configuration keys
    protected const KEY_MODE = 'mode';
    protected const KEY_API_ID = 'api_id';
    protected const KEY_API_SECRET = 'api_secret';
    protected const KEY_PUBLIC_KEY = 'public_key';
    protected const KEY_TEST_API_ID = 'test_api_id';
    protected const KEY_TEST_API_SECRET = 'test_api_secret';
    protected const KEY_TEST_PUBLIC_KEY = 'test_public_key';
    protected const KEY_COMPANY = 'company';
    protected const KEY_MID = 'mid';
    protected const KEY_DEBUG_LOG = 'debug_log';
    protected const HOSTED_PAYMENT_PRODUCTION_URL = 'https://pay.acquired.com/v1/';
    protected const HOSTED_PAYMENT_TEST_URL = 'https://test-pay.acquired.com/v1/';


    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Determines if the payment methods are operating in production mode.
     *
     * @return bool True if in production mode, false if in test mode.
     */
    public function getMode(): bool
    {
        return $this->isSetFlag(self::KEY_MODE);
    }

    /**
     * Retrieves the appropriate API ID based on the current mode (test or production).
     *
     * @return string|null The API ID for the current mode.
     */
    public function getApiId(): ?string
    {
        return $this->getValue($this->getMode() ? self::KEY_API_ID :  self::KEY_TEST_API_ID);
    }

    /**
     * Retrieves the appropriate API Secret based on the current mode (test or production).
     *
     * @return string|null The API Secret for the current mode.
     */
    public function getApiSecret(): ?string
    {
        return $this->getValue($this->getMode() ? self::KEY_API_SECRET :  self::KEY_TEST_API_SECRET);
    }

    /**
     * Retrieves the appropriate Public Key based on the current mode (test or production).
     *
     * @return string|null The Public Key for the current mode.
     */
    public function getPublicKey(): ?string
    {
        return $this->getValue($this->getMode() ? self::KEY_PUBLIC_KEY :  self::KEY_TEST_PUBLIC_KEY);
    }

    /**
     * Retrieves the company name.
     *
     * @return string|null The company name.
     */
    public function getCompanyName(): ?string
    {
        return $this->getValue(self::KEY_COMPANY);
    }

    /**
     * Retrieves the MID (Merchant ID).
     *
     * @return string|null The MID.
     */
    public function getMid(): ?string
    {
        return $this->getValue(self::KEY_MID);
    }

    /**
     * Checks if debug logging is enabled for the Acquired payment methods.
     *
     * @return bool True if debug logging is enabled, otherwise false.
     */
    public function isDebugLogEnabled(): bool
    {
        return $this->isSetFlag(self::KEY_DEBUG_LOG);
    }

    /**
     * Returns hosted payment payment URL
     *
     * @return string
     */
    public function getRedirectUrl(): string
    {
        $url = $this->getMode() ? self::HOSTED_PAYMENT_PRODUCTION_URL : self::HOSTED_PAYMENT_TEST_URL;
        return $url;
    }

    /**
     * Retrieves a configuration value based on the specified key.
     *
     * @param string $key The configuration key to retrieve the value for.
     * @return mixed The configuration value.
     */
    private function getValue(string $key): mixed
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH . $key,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Checks if a configuration flag is set based on the specified key.
     *
     * @param string $key The configuration key to check the flag for.
     * @return bool True if the flag is set, otherwise false.
     */
    private function isSetFlag(string $key): bool {
        return $this->scopeConfig->isSetFlag(
            self::CONFIG_PATH . $key,
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
