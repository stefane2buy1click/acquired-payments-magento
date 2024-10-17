<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Gateway\Http\Client;

use Exception;
use Psr\Log\LoggerInterface;
use Acquired\Payments\Client\Gateway;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\Http\ClientInterface;
use Acquired\Payments\Exception\Command\RequestException;

class Capture implements ClientInterface
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
     * @return array|null
     * @throws RequestException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $body = $transferObject->getBody();

        $this->logger->debug(
            __('Place Capture transaction request.'),
            [
                'body' => $body
            ]
        );

        try {
            if ($body['is_captured']) {
                $transaction = $this->gateway->getTransaction()->get($body['transaction_id']);
            } else {
                $transaction = $this->gateway->getTransaction()->capture(
                    $body['transaction_id'],
                    $body['amount']
                );
            }

            $this->logger->debug(
                __('Capture transaction request placed successfully.'),
                [
                    'response' => $transaction
                ]
            );

        } catch (Exception $e) {
            $this->logger->critical(
                __('Capture transaction request failed: %1', $e->getMessage()),
                [
                    'exception' => $e
                ]
            );

            throw new RequestException(__($e->getMessage()));
        }

        return $transaction;
    }
}
