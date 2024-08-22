<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Gateway\Http\Client;

use Exception;
use Psr\Log\LoggerInterface;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Acquired\Payments\Client\Gateway;
use Acquired\Payments\Exception\Command\RequestException;

class Sale implements ClientInterface
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
            __('Place Sale transaction request.'),
            [
                'body' => $body
            ]
        );

        try {
            $transaction = $this->gateway->getTransaction()->get($body['transaction_id']);
            $this->logger->debug(
                __('Sale transaction request placed successfully.'),
                [
                    'response' => $transaction
                ]
            );
        } catch (Exception $e) {
            $this->logger->critical(
                __('Sale transaction request failed: %1', $e->getMessage()),
                [
                    'exception' => $e
                ]
            );

            throw new RequestException(__($e->getMessage()));
        }

        return $transaction;
    }
}
