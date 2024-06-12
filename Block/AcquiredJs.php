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

namespace Acquired\Payments\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class AcquiredJs extends Template
{

    const XML_CONFIG_PATH_JS_INTEGRITY_HASH = 'payment/acquired/configuration/js_integrity_hash';

    protected $_template = 'js.phtml';

    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getJsIntegrityHash(): ?string
    {
        return $this->_scopeConfig->getValue(self::XML_CONFIG_PATH_JS_INTEGRITY_HASH);
    }

}