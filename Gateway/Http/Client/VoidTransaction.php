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

namespace Acquired\Payments\Gateway\Http\Client;

use Exception;
use Psr\Log\LoggerInterface;
use Acquired\Payments\Client\Gateway;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Acquired\Payments\Exception\Command\RequestException;

class VoidTransaction implements ClientInterface
{

    /**
     * @param Gateway $gateway
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Gateway $gateway,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param TransferInterface $transferObject
     * @return array
     * @throws RequestException
     */
    public function placeRequest(TransferInterface $transferObject): array
    {
        $body = $transferObject->getBody();
        $this->logger->debug(
            __('Place Void transaction request.'),
            [
                'body' => $body
            ]
        );

        try {
            $transaction = $this->gateway->getTransaction()->void($body['transaction_id']);
            if ($transaction['status'] === 'declined') {
                $transaction['title'] = 'Void transaction declined by payment gateway';
            }

            $this->logger->debug(
                __('Void transaction request placed successfully.'),
                [
                    'response' => $transaction
                ]
            );
        } catch (Exception $e) {
            $this->logger->critical(
                __('Void transaction request failed: %1', $e->getMessage()),
                [
                    'exception' => $e
                ]
            );

            throw new RequestException(__($e->getMessage()));
        }

        return $transaction;
    }
}
