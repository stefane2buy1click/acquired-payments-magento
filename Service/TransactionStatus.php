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

        /*if ($this->dateTime->gmtTimestamp() - strtotime($transaction['created']) > 86400) {
            return true;
        } else {
            return false;
        }*/
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
