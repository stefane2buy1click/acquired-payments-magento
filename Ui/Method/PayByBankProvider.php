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

namespace Acquired\Payments\Ui\Method;

use Exception;
use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Acquired\Payments\Gateway\Config\Basic;
use Acquired\Payments\Gateway\Config\Card\Config as CardConfig;
use Acquired\Payments\Gateway\Config\Hosted\Config as HostedConfig;

class PayByBankProvider implements ConfigProviderInterface
{
    public const CODE = 'acquired_pay_by_bank';

    /**
     * @param CardConfig $cardConfig
     * @param Basic $basicConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly CardConfig $cardConfig,
        private readonly HostedConfig $hostedConfig,
        private readonly Basic $basicConfig,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {
        try {
            return [
                'payment' => [
                    self::CODE => [
                        'public_key' => $this->basicConfig->getPublicKey(),
                        'mode' => (bool) $this->basicConfig->getMode() ? 'production' : 'test',
                        'active' => $this->hostedConfig->isActive(),
                        'title' => $this->hostedConfig->getTitle(),
                    ]
                ]
            ];
        } catch (Exception $e) {
            $this->logger->critical(
                __('Error fetching payment config: %1', $e->getMessage()),
                [
                    'exception' => $e
                ]
            );

            return [];
        }
    }
}
