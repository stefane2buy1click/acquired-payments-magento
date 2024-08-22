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
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Acquired\Payments\Client\Gateway;
use Acquired\Payments\Exception\Command\RequestException;

class HostedCheckout implements ClientInterface
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

        $registry = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Registry');
        $order = $registry->registry('acq_transfer_order');
        $quote = $order ? $order->getQuote() : null;

        if (!$quote || ($quote && !$quote->getIsMultiShipping())) {
            try {
                $response = $this->gateway->getPaymentLinks()->generateLinkId($body);
                $this->logger->debug(
                    __('Sale transaction request placed successfully.'),
                    [
                        'response' => $response
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
        } else {
            $response = [
                'status' => 'success',
                'link_id' => 'Quote-' . $quote->getId()
            ];
        }

        return $response;
    }
}
