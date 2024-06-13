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

namespace Acquired\Payments\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AcquiredCustomer extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'acquired_customer_resource_model';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('acquired_customer', 'id');
        $this->_useIsObjectNew = true;
    }
}
