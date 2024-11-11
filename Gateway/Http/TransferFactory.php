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

namespace Acquired\Payments\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\Http\Transfer;

class TransferFactory implements TransferFactoryInterface
{
    /**
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        private readonly TransferBuilder $transferBuilder
    ) {
    }

    /**
     * @inheritDoc
     */
    public function create(array $request): TransferInterface|Transfer
    {
        return $this->transferBuilder->setBody($request)->build();
    }
}
