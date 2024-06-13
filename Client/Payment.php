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

namespace Acquired\Payments\Client;

use Magento\Framework\Exception\LocalizedException;

class Payment extends AbstractClient
{
    public const TYPE_CARD = 'card';

    public const TYPE_SAVED_CARD = 'reuse';

    public const TYPE_APPLEPAY = 'apple_pay';

    public const TYPE_GOOGLEPAY = 'google_pay';

    public const TYPE_RECURRING = 'recurring';

    private const TYPES = [
        self::TYPE_CARD => 'payments',
        self::TYPE_SAVED_CARD => 'payments/reuse',
        self::TYPE_APPLEPAY => 'payments/apple-pay',
        self::TYPE_GOOGLEPAY => 'payments/google-pay',
        self::TYPE_RECURRING => 'payments/recurring'
    ];

    /**
     * Process a payment for method
     *
     * @param array $payload
     * @param string $type
     * @return array|null
     * @throws LocalizedException
     * @throws \Exception
     */
    public function process(array $payload, string $type): ?array
    {
        if (!isset(self::TYPES[$type])) {
            throw new LocalizedException(__('Unsupported type: %1', $type));
        }
        return $this->call('post', self::TYPES[$type], $payload);
    }
}
