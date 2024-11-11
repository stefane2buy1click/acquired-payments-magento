<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Model\System\Config;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Payment\Model\MethodInterface;

class PaymentAction implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => MethodInterface::ACTION_AUTHORIZE, 'label' => __('Authorize')],
            ['value' => MethodInterface::ACTION_AUTHORIZE_CAPTURE, 'label' => __('Authorize & Capture')]
        ];
    }
}
