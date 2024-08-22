<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Service;

use Acquired\Payments\Client\Gateway;
use Magento\Framework\Stdlib\DateTime\DateTime;

class TransactionStatus
{
    private ?array $transaction = [];

    /**
     * @param Gateway $gateway
     * @param DateTime $dateTime
     */
    public function __construct(
        private readonly Gateway $gateway,
        private readonly DateTime $dateTime
    ) {
    }

    /**
     * Check if invoice can be refunded
     *
     * @param string $transactionId
     * @return bool
     * @throws \Exception
     */
    public function canRefundInvoice(string $transactionId): bool
    {
        $transaction = $this->getTransaction($transactionId);

        if (in_array($transaction['transaction_type'], ['void', 'refund'])) {
            return false;
        }

        return true;
    }

    /**
     * Check if invoice can be voided
     *
     * @param string $transactionId
     * @return bool
     * @throws \Exception
     */
    public function canVoidInvoice(string $transactionId): bool
    {
        $transaction = $this->getTransaction($transactionId);

        if (in_array($transaction['transaction_type'], ['void', 'refund'])) {
            return false;
        }

        return !$this->canRefundInvoice($transactionId);
    }

    /**
     * Get transaction details
     *
     * @param string $transactionId
     * @return array
     * @throws \Exception
     */
    private function getTransaction(string $transactionId): array
    {
        if (empty($this->transaction)) {
            $this->transaction = $this->gateway->getTransaction()->get($transactionId);
        }

        return $this->transaction;
    }
}
