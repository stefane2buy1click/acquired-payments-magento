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

namespace Acquired\Payments\Api\Data;

interface ApplePaySessionDataInterface
{

    /**
     * Set Display Name
     *
     * @param string $displayName
     * @return self
     */
    public function setDisplayName(string $displayName): self;

    /**
     * Get Session Id
     *
     * @return string|null
     */
    public function getDisplayName(): ?string;

    /**
     * Set Domain Name
     *
     * @param string $domainName
     * @return self
     */
    public function setDomainName(string $domainName): self;

    /**
     * Get Domain Name
     *
     * @return string|null
     */
    public function getDomainName(): ?string;

    /**
     * Set Epoch Timestamp
     *
     * @param int $epochTimestamp
     * @return self
     */
    public function setEpochTimestamp(int $epochTimestamp): self;

    /**
     * Get Epoch Timestamp
     *
     * @return int|null
     */
    public function getEpochTimestamp(): ?int;

    /**
     * Set Expires At
     *
     * @param int $expiresAt
     * @return self
     */
    public function setExpiresAt(int $expiresAt): self;

    /**
     * Get Expires At
     *
     * @return int|null
     */
    public function getExpiresAt(): ?int;

    /**
     * Set Merchant Identifier
     *
     * @param string $merchantIdentifier
     * @return self
     */
    public function setMerchantIdentifier(string $merchantIdentifier): self;

    /**
     * Get Merchant Identifier
     *
     * @return string|null
     */
    public function getMerchantIdentifier(): ?string;

    /**
     * Set Merchant Session Identifier
     *
     * @param string $merchantSessionIdentifier
     * @return self
     */
    public function setMerchantSessionIdentifier(string $merchantSessionIdentifier): self;

    /**
     * Get Merchant Session Identifier
     *
     * @return string|null
     */
    public function getMerchantSessionIdentifier(): ?string;

    /**
     * Set Nonce
     *
     * @param string $nonce
     * @return self
     */
    public function setNonce(string $nonce): self;

    /**
     * Get Nonce
     *
     * @return string|null
     */
    public function getNonce(): ?string;

    /**
     * Set Operational Analytics Identifier
     *
     * @param string $operationalAnalyticsIdentifier
     * @return self
     */
    public function setOperationalAnalyticsIdentifier(string $operationalAnalyticsIdentifier): self;

    /**
     * Get Operational Analytics Identifier
     *
     * @return string|null
     */
    public function getOperationalAnalyticsIdentifier(): ?string;

    /**
     * Set PSP Id
     *
     * @param string $pspId
     * @return self
     */
    public function setPspId(string $pspId): self;

    /**
     * Get PSP Id
     *
     * @return string|null
     */
    public function getPspId(): ?string;

    /**
     * Set Retries
     *
     * @param int $retries
     * @return self
     */
    public function setRetries(int $retries): self;

    /**
     * Get Retries
     *
     * @return int|null
     */
    public function getRetries(): ?int;

    /**
     * Set Signature
     *
     * @param string $signature
     * @return self
     */
    public function setSignature(string $signature): self;

    /**
     * Get Signature
     *
     * @return string|null
     */
    public function getSignature(): ?string;
}