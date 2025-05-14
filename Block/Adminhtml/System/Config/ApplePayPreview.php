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

namespace Acquired\Payments\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ApplePayPreview extends Field
{
    protected function _construct()
    {
        $this->setTemplate('Acquired_Payments::system/config/apple-pay-preview-button.phtml');
        parent::_construct();
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
}
