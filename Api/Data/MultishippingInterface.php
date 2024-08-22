<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Api\Data;

interface MultishippingInterface
{

    const ID = 'id';
    const MULTISHIPPING_ID = 'id';
    const QUOTE_RESERVED_ID = 'quote_reserved_id';
    const QUOTE_ADDRESS_ID = 'quote_address_id';
    const CUSTOMER_ID = 'customer_id';
    const ACQUIRED_SESSION_ID = 'acquired_session_id';
    const ACQUIRED_TRANSACTION_ID = 'acquired_transaction_id';
    const STATUS = 'status';

    const STATUS_NEW = 'new';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    /**
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     * @param int $id
     * @return \Acquired\Payments\Multishipping\Api\Data\MultishippingInterface
     */
    public function setId(int $id);

    /**
     * Get multishipping_id
     * @return int|null
     */
    public function getMultishippingId();

    /**
     * Set multishipping_id
     * @param int $multishippingId
     * @return \Acquired\Payments\Multishipping\Api\Data\MultishippingInterface
     */
    public function setMultishippingId(int $multishippingId);

    /**
     * Get quote_reserved_id
     * @return string|null
     */
    public function getQuoteReservedId();

    /**
     * Set quote_reserved_id
     * @param string $quoteReservedId
     * @return \Acquired\Payments\Multishipping\Api\Data\MultishippingInterface
     */
    public function setQuoteReservedId(string $quoteReservedId);

    /**
     * Get quote_address_id
     * @return string|null
     */
    public function getQuoteAddressId() : ?string;

    /**
     * Set quote_address_id
     * @param int $quoteAddressId
     * @return \Acquired\Payments\Multishipping\Api\Data\MultishippingInterface
     */
    public function setQuoteAddressId(int $quoteAddressId);

    /**
     * Get customer_id
     * @return int|null
     */
    public function getCustomerId() : ?int;

    /**
     * Set customer_id
     * @param int $customerId
     * @return \Acquired\Payments\Multishipping\Api\Data\MultishippingInterface
     */
    public function setCustomerId(int $customerId);

    /**
     * Get acquired_session_id
     * @return string|null
     */
    public function getAcquiredSessionId() : ?string;

    /**
     * Set acquired_session_id
     * @param string $acquiredSessionId
     * @return \Acquired\Payments\Multishipping\Api\Data\MultishippingInterface
     */
    public function setAcquiredSessionId(string $acquiredSessionId);

    /**
     * Get acquired_transaction_id
     * @return string|null
     */
    public function getAcquiredTransactionId() : ?string;

    /**
     * Set acquired_transaction_id
     * @param string $acquiredTransactionId
     * @return \Acquired\Payments\Multishipping\Api\Data\MultishippingInterface
     */
    public function setAcquiredTransactionId(string $acquiredTransactionId);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus() : ?string;

    /**
     * Set status
     * @param string $status
     * @return \Acquired\Payments\Multishipping\Api\Data\MultishippingInterface
     */
    public function setStatus(string $status);



}
