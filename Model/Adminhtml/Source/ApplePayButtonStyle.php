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

namespace Acquired\Payments\Model\Adminhtml\Source;

class ApplePayButtonStyle
{
    public function toOptionArray()
    {
        return [
            [
                'value' => "black",
                'label' => __('A black button with white lettering (default)')
            ],
            [
                'value' => "white",
                'label' => __('A white button with black lettering')
            ],
            [
                'value' => "white-outline",
                'label' => __('A white button with black lettering and a black outline')
            ],
        ];
    }
}