<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Model;

use Acquired\Payments\Api\Data\AcquiredCustomerInterface;
use Acquired\Payments\Model\ResourceModel\AcquiredCustomer as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class AcquiredCustomer extends AbstractModel implements AcquiredCustomerInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'acquired_customer_model';

    /**
     * Initialize magento model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function setAcquiredCustomerId(string $acquiredCustomerId): AcquiredCustomerInterface
    {
        return $this->setData(self::ACQUIRED_CUSTOMER_ID, $acquiredCustomerId);
    }

    /**
     * @inheritDoc
     */
    public function getAcquiredCustomerId(): ?string
    {
        return $this->getData(self::ACQUIRED_CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId(int $customerId): AcquiredCustomerInterface
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId(): ?int
    {
        return $this->getData(self::CUSTOMER_ID)
            ? (int) $this->getData(self::CUSTOMER_ID)
            : null;
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return (string) $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): ?string
    {
        return (string) $this->getData(self::UPDATED_AT);
    }
}
