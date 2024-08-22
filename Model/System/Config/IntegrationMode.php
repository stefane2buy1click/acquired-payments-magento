<?php
declare(strict_types=1);

/**
 *
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 *
 *
 */

namespace Acquired\Payments\Model\System\Config;

use Magento\Framework\Data\OptionSourceInterface;

class IntegrationMode implements OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 1, 'label' => __('Production')],
            ['value' => 0, 'label' => __('Test')]
        ];
    }
}
