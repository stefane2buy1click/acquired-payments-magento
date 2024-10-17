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
use Acquired\Payments\Service\TransactionStatus;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Acquired\Payments\Exception\Command\RequestException;

class Refund implements ClientInterface
{

    /**
     * @param Gateway $gateway
     * @param TransactionStatus $transactionStatus
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Gateway $gateway,
        private readonly TransactionStatus $transactionStatus,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param TransferInterface $transferObject
     * @return array|null
     * @throws RequestException
     */
    public function placeRequest(TransferInterface $transferObject): ?array
    {
        $body = $transferObject->getBody();
        $this->logger->debug(
            __('Place Refund transaction request.'),
            [
                'body' => $body
            ]
        );

        try {
            if (!$this->transactionStatus->canRefundInvoice($body['transaction_id'])) {
                return [
                    'status' => 'declined',
                    'title' => __('ACQUIRED.com cannot refund transaction that is less than 24h old')
                ];
            }

            $response = $this->gateway->getTransaction()->refund(
                $body['transaction_id'],
                $body['reference']
            );

            /*
             * check if refund is declined for whatever reason, possibly 24 hours has not passed
             * if yes, try to void it if full amount is requested
             */
            if (isset($response['status']) && $response['status'] === 'declined') {
                if ($body['reference']['amount'] == $body['grand_total']) {
                    $response = $this->gateway->getTransaction()->void($body['transaction_id']);

                    if ($response['status'] === 'declined') {
                        throw new RequestException(__('Void transaction declined by payment gateway.'));
                    }
                } else {
                    throw new RequestException(__('We currently cannot process partial refunds. Please try again tomorrow or opt for a full refund instead.'));
                }
            }

            $this->logger->debug(
                __('Refund transaction request placed successfully.'),
                [
                    'response' => $response
                ]
            );

        } catch (Exception $e) {
            $this->logger->critical(
                __('Refund transaction request failed: %1', $e->getMessage()),
                [
                    'exception' => $e
                ]
            );

            throw new RequestException(__($e->getMessage()));
        }

        return $response;
    }
}
