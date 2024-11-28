<?php

/**
 * Acquired.com Payments Integration for Magento2
 *
 * Copyright (c) 2024 Acquired Limited (https://acquired.com/)
 *
 * This file is open source under the MIT license.
 * Please see LICENSE file for more details.
 */

namespace Acquired\Payments\Model\System\Config;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * @class TdsChallengePreference
 *
 * Provides options for selecting 3-D Secure transaction challenge preferences in the admin panel.
 */
class TdsChallengePreference implements OptionSourceInterface
{

    /**
     * Prepare options for 3-D Secure Challenge Preference
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'challenge_mandated', 'label' => __('Challenge Mandated')],
            ['value' => 'challenge_preferred', 'label' => __('Challenge Preferred')],
            ['value' => 'no_challenge_requested', 'label' => __('No Challenge Requested')],
            ['value' => 'no_preference', 'label' => __('No Preference')]
        ];
    }
}
