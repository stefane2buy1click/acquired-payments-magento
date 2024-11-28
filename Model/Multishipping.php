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

namespace Acquired\Payments\Model;

use Acquired\Payments\Api\Data\MultishippingInterface;
use Magento\Framework\Model\AbstractModel;

class Multishipping extends AbstractModel implements MultishippingInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Acquired\Payments\Model\ResourceModel\Multishipping::class);
    }

    /**
     * @inheritDoc
     */
    public function getMultishippingId()
    {
        return $this->getData(self::MULTISHIPPING_ID);
    }

    /**
     * @inheritDoc
     */
    public function setMultishippingId($multishippingId)
    {
        return $this->setData(self::MULTISHIPPING_ID, $multishippingId);
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId($id)
    {
        return $this->setData(self::ORDER_ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getQuoteReservedId() : ?string
    {
        return $this->getData(self::QUOTE_RESERVED_ID);
    }

    /**
     * @inheritDoc
     */
    public function setQuoteReservedId(string $quoteReservedId)
    {
        return $this->setData(self::QUOTE_RESERVED_ID, $quoteReservedId);
    }

    /**
     * @inheritDoc
     */
    public function getQuoteAddressId() : ?string
    {
        return $this->getData(self::QUOTE_ADDRESS_ID);
    }

    /**
     * @inheritDoc
     */
    public function setQuoteAddressId(int $quoteAddressId)
    {
        return $this->setData(self::QUOTE_ADDRESS_ID, $quoteAddressId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId() : ?int
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId(int $customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getAcquiredSessionId() : ?string
    {
        return $this->getData(self::ACQUIRED_SESSION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setAcquiredSessionId(string $acquiredSessionId)
    {
       return $this->setData(self::ACQUIRED_SESSION_ID, $acquiredSessionId);
    }

    /**
     * @inheritDoc
     */
    public function getAcquiredTransactionId() : ?string
    {
        return $this->getData(self::ACQUIRED_TRANSACTION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setAcquiredTransactionId(string $acquiredTransactionId)
    {
        return $this->setData(self::ACQUIRED_TRANSACTION_ID, $acquiredTransactionId);
    }

    /**
     * @inheritDoc
     */
    public function getStatus() : ?string
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus(string $status)
    {
        return $this->setData(self::STATUS, $status);
    }
}