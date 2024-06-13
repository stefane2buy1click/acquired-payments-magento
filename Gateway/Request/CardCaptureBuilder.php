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

namespace Acquired\Payments\Gateway\Request;

use Exception;
use Psr\Log\LoggerInterface;
use Acquired\Payments\Exception\Command\BuilderException;
use Acquired\Payments\Gateway\Config\Card\Config as CardConfig;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class CardCaptureBuilder implements BuilderInterface
{

    /**
     * @param CardConfig $cardConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly CardConfig $cardConfig,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param array $buildSubject
     * @return array
     * @throws BuilderException
     */
    public function build(array $buildSubject): array
    {
        try {
            $payment = SubjectReader::readPayment($buildSubject)->getPayment();
            if (empty($payment->getAdditionalInformation('transaction_id'))) {
                throw new BuilderException(__('Missing transaction_id'));
            }

            return [
                'transaction_id' => $payment->getAdditionalInformation('transaction_id'),
                'amount' => ['amount' => SubjectReader::readAmount($buildSubject)],
                'is_captured' => $this->cardConfig->getCaptureAction()
            ];

        } catch (Exception $e) {
            $message = __('Capture build failed: %1', $e->getMessage());
            $this->logger->critical($message, ['exception' => $e]);

            throw new BuilderException($message);
        }
    }
}