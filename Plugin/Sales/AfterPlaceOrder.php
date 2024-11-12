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

namespace Acquired\Payments\Plugin\Sales;

use Acquired\Payments\Ui\Method\PayByBankProvider;

class AfterPlaceOrder
{

    public function __construct(
        private readonly \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        private readonly \Acquired\Payments\Gateway\Config\Basic $basicConfig
    ) {
    }

    public function afterSavePaymentInformationAndPlaceOrder(
        $subject,
        $result
    ) {
        $order = $this->orderRepository->get($result);

        switch ($order->getPayment()->getMethod()) {
            case PayByBankProvider::CODE:
                return $this->getPayByBankData($order);
            default:
                break;
        }

        return $result;
    }

    private function getPayByBankData($order)
    {
        $redirectUrl = $this->getRedirectUrl() . $order->getPayment()->getLastTransId();
        return $redirectUrl;
    }

    private function getRedirectUrl(): string
    {
        $url = $this->basicConfig->getRedirectUrl();
        return $url;
    }
}
