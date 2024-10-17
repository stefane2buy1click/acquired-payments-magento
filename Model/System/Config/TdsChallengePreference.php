<?php

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
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
