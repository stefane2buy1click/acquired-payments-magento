<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Model\ResourceModel\AcquiredCustomer;

use Acquired\Payments\Model\AcquiredCustomer as Model;
use Acquired\Payments\Model\ResourceModel\AcquiredCustomer as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'acquired_customer_collection';

    /**
     * Initialize collection model.
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
