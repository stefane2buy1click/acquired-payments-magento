<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
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
