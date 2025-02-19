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

interface PaymentIntentInterface
{
    public const PAYMENT_INTENT_ID = 'payment_intent_id';
    public const QUOTE_ID = 'quote_id';
    public const SESSION_ID = 'session_id';
    public const NONCE = 'nonce';
    public const FINGERPRINT = 'fingerprint';
    public const FINGERPRINT_DATA = 'fingerprint_data';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * Set Payment Intent Id
     *
     * @param string $paymentIntentId
     * @return void
     */
    public function setPaymentIntentId(string $paymentIntentId): void;

    /**
     * Get Payment Intent Id
     *
     * @return int|null
     */
    public function getPaymentIntentId(): ?int;

    /**
     * Set Quote Id
     *
     * @param int $quoteId
     * @return void
     */
    public function setQuoteId(int $quoteId): void;

    /**
     * Get Quote Id
     *
     * @return int|null
     */
    public function getQuoteId(): ?int;

    /**
     * Set Session Id
     *
     * @param string $sessionId
     * @return void
     */
    public function setSessionId(string $sessionId): void;

    /**
     * Get Session Id
     *
     * @return string|null
     */
    public function getSessionId(): ?string;

    /**
     * Set Nonce
     *
     * @param string|null $nonce
     */
    public function setNonce(?string $nonce): void;

    /**
     * Get Nonce
     *
     * @return string|null
     */
    public function getNonce(): ?string;

    /**
     * Set Fingerprint
     *
     * @param string $fingerprint
     */
    public function setFingerprint(string $fingerprint): void;

    /**
     * Get Fingerprint
     *
     * @return string|null
     */
    public function getFingerprint(): ?string;

    /**
     * Set Fingerprint Data
     *
     * @param string $fingerprintData
     */
    public function setFingerprintData(string $fingerprintData): void;

    /**
     * Get Fingerprint Data
     *
     * @return string|null
     */
    public function getFingerprintData(): ?string;

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Get Updated At
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;
}
