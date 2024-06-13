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

class Gateway
{
    /**
     * @param Bank $bank
     * @param Card $card
     * @param Component $component
     * @param Customer $customer
     * @param MerchantSession $merchantSession
     * @param Payment $payment
     * @param Transaction $transaction
     */
    public function __construct(
        private readonly Bank $bank,
        private readonly Card $card,
        private readonly Component $component,
        private readonly Customer $customer,
        private readonly MerchantSession $merchantSession,
        private readonly Payment $payment,
        private readonly Transaction $transaction
    ) {
    }

    /**
     * Get Bank API
     *
     * @return Bank
     */
    public function getPayByBank(): Bank
    {
        return $this->bank;
    }

    /**
     * Get Card API
     *
     * @return Card
     */
    public function getCard(): Card
    {
        return $this->card;
    }

    /**
     * Get Component API
     *
     * @return Component
     */
    public function getComponent(): Component
    {
        return $this->component;
    }

    /**
     * Get Customer API
     *
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * Get MerchantSession API
     *
     * @return MerchantSession
     */
    public function getMerchantSession(): MerchantSession
    {
        return $this->merchantSession;
    }

    /**
     * Get Payment API
     *
     * @return Payment
     */
    public function getPayment(): Payment
    {
        return $this->payment;
    }

    /**
     * Get Transaction API
     *
     * @return Transaction
     */
    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }
}
