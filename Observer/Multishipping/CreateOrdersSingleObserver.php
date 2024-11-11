<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Observer\Multishipping;

use Acquired\Payments\Service\MultishippingService;
use Magento\Framework\Event\Observer;
use Acquired\Payments\Ui\Method\CardProvider;
use Acquired\Payments\Ui\Method\PayByBankProvider;

class CreateOrdersSingleObserver implements \Magento\Framework\Event\ObserverInterface
{

    public function __construct(
        private readonly MultishippingService $multishippingService
    ) {
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $address = $observer->getEvent()->getAddress();
        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();

        $paymentProviderCode = $quote->getPayment()->getMethod();

        $supportedMethods = [
            CardProvider::CODE,
            PayByBankProvider::CODE
        ];

        if (in_array($paymentProviderCode, $supportedMethods)) {
            $this->multishippingService->reserveOrderIds($quote);

            if ($address->getId()) {
                $multishipping = $this->multishippingService->getMultishippingByAddressId((int) $address->getId());

                if ($multishipping) {
                    $order->setIncrementId($multishipping->getQuoteReservedId());

                    $multishipping->setOrderId($order->getId());
                    $multishipping->save();

                    if ($multishipping->getAcquiredTransactionId()) {
                        $order->setMultishippingAcquiredTransactionId($multishipping->getAcquiredTransactionId());
                        $payment->setLastTransId($multishipping->getAcquiredTransactionId());
                        $payment->setTransactionId($multishipping->getAcquiredTransactionId());
                    }
                }
            }
        }

        return $this;
    }
}
