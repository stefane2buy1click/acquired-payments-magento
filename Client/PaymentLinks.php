<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Client;

use Magento\Framework\Exception\LocalizedException;

class PaymentLinks extends AbstractClient
{
    /**
     * Process a payment for method
     *
     * @param array $payload
     * @param string $type
     * @return array|null
     * @throws LocalizedException
     * @throws \Exception
     */
    public function generateLinkId(array $payload): ?array
    {
        return $this->call('post', 'payment-links', $payload);
    }
}
