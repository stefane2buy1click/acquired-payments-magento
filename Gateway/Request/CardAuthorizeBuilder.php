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
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Acquired\Payments\Exception\Command\BuilderException;

class CardAuthorizeBuilder implements BuilderInterface
{

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly LoggerInterface $logger
    ){
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
                'transaction_id' => $payment->getAdditionalInformation('transaction_id')
            ];

        } catch (Exception $e) {
            $message = __('Authorize build failed: %1', $e->getMessage());
            $this->logger->critical($message, ['exception' => $e]);

            throw new BuilderException($message);
        }
    }
}
