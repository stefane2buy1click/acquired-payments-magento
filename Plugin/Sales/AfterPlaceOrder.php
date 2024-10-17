<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Plugin\Sales;

class AfterPlaceOrder
{

    public function __construct(
        private readonly \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        private readonly \Acquired\Payments\Gateway\Config\Basic $basicConfig
    ) { }

    public function afterSavePaymentInformationAndPlaceOrder(
        $subject,
        $result
    ) {
        $order = $this->orderRepository->get($result);

        switch ($order->getPayment()->getMethod())
        {
            case 'acquired_pay_by_bank':
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
