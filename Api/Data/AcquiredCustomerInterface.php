<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Api\Data;

interface AcquiredCustomerInterface
{
    public const ACQUIRED_CUSTOMER_ID = 'acquired_customer_id';

    public const CUSTOMER_ID = 'customer_id';

    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = 'updated_at';

    /**
     * Set acquired customer id
     *
     * @param string $acquiredCustomerId
     * @return self
     */
    public function setAcquiredCustomerId(string $acquiredCustomerId): self;

    /**
     * Get acquired customer id
     *
     * @return string|null
     */
    public function getAcquiredCustomerId(): ?string;

    /**
     * Set Customer Id
     *
     * @param int $customerId
     * @return self
     */
    public function setCustomerId(int $customerId): self;

    /**
     * Get Customer Id
     *
     * @return int|null
     */
    public function getCustomerId(): ?int;

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
