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

namespace Acquired\Payments\Model\Payment;

use Acquired\Payments\Api\Data\PaymentIntentInterface;
use Acquired\Payments\Model\ResourceModel\Payment\Intent as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class Intent extends AbstractModel implements PaymentIntentInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'acquired_payment_intent';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheritdoc
     */
    public function setPaymentIntentId(string $paymentIntentId): void
    {
        $this->setData(self::PAYMENT_INTENT_ID, $paymentIntentId);
    }

    /**
     * @inheritdoc
     */
    public function getPaymentIntentId(): ?int
    {
        return (int) $this->_getData(self::PAYMENT_INTENT_ID) ?: null;
    }

    /**
     * @inheritdoc
     */
    public function setQuoteId(int $quoteId): void
    {
        $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * @inheritdoc
     */
    public function getQuoteId(): ?int
    {
        return $this->_getData(self::QUOTE_ID) ?: null;
    }

    /**
     * @inheritdoc
     */
    public function setSessionId(string $sessionId): void
    {
        $this->setData(self::SESSION_ID, $sessionId);
    }

    /**
     * @inheritdoc
     */
    public function getSessionId(): ?string
    {
        return $this->_getData(self::SESSION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setNonce(?string $nonce): void
    {
        $this->setData(self::NONCE, $nonce);
    }

    /**
     * @inheritdoc
     */
    public function getNonce(): ?string
    {
        return $this->_getData(self::NONCE);
    }

    /**
     * @inheritdoc
     */
    public function setFingerprint(string $fingerprint): void
    {
        $this->setData(self::FINGERPRINT, $fingerprint);
    }

    /**
     * @inheritdoc
     */
    public function getFingerprint(): ?string
    {
        return $this->_getData(self::FINGERPRINT);
    }

    /**
     * @inheritdoc
     */
    public function setFingerprintData(string $fingerprintData): void
    {
        $this->setData(self::FINGERPRINT_DATA, $fingerprintData);
    }

    /**
     * @inheritdoc
     */
    public function getFingerprintData(): ?string
    {
        return $this->_getData(self::FINGERPRINT_DATA);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->_getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->_getData(self::UPDATED_AT);
    }
}
