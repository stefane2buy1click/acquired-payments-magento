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

namespace Acquired\Payments\Block\Adminhtml\Order\Payment;

use Exception;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form\Cc;
use Magento\Payment\Model\Config;
use Acquired\Payments\Gateway\Config\Basic;
use Acquired\Payments\Gateway\Config\Card\Config as CardConfig;

class Form extends Cc
{

    protected $_template = 'Acquired_Payments::payment/card.phtml';

    /**
     * @param Context $context
     * @param Config $paymentConfig
     * @param SerializerInterface $serializer
     * @param Basic $basicConf
     * @param CardConfig $cardConf
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $paymentConfig,
        private readonly SerializerInterface $serializer,
        private readonly Basic $basicConf,
        private readonly CardConfig $cardConf,
        private readonly LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
    }

    /**
     * Get payment method config values
     *
     * @return string
     */
    public function getConfig(): string
    {
        try {
            $configData = [
                'public_key' => $this->basicConf->getPublicKey(),
                'mode' => $this->basicConf->getMode() ? 'production' : 'test',
                'style' => $this->cardConf->getStyle(),
                'error' => false,
                'message' => null,
            ];
        } catch (Exception $e) {
            $this->logger->critical(
                __('Admin error fetching payment config: %1', $e->getMessage()),
                [
                    'exception' => $e
                ]
            );
            $configData['error'] = true;
            $configData['message'] = __($e->getMessage());
        }

        return $this->serializer->serialize($configData);
    }
}
