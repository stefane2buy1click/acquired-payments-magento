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

namespace Acquired\Payments\Cron;

use Psr\Log\LoggerInterface;
use Acquired\Payments\Model\MultishippingFactory;
use Acquired\Payments\Client\Gateway;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class CleanMultishipping
{
    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param MultishippingFactory $multishippingFactory
     * @param Gateway $gateway
     * @param TimezoneInterface $dateTime
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly MultishippingFactory $multishippingFactory,
        private readonly Gateway $gateway,
        private readonly TimezoneInterface $dateTime
    ) {
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $dateTime = new \DateTime($this->dateTime->date()->format('Y-m-d H:i:s'));

        $multishipping = $this->multishippingFactory->create();
        $collection = $multishipping->getCollection()->addFieldToFilter('status', 'new');

        if ($collection->count() > 0) {
            foreach ($collection as $result) {

                $difference = $dateTime->diff(new \DateTime($result->getCreatedAt()));

                // we are skipping transactions less than 10 mintues old
                if ($difference->i < 10) continue;

                try {
                    $response = $this->gateway->getTransaction()->void($result->getAcquiredTransactionId());

                    if (isset($response['status']) && $response['status'] === 'success') {
                        $this->logger->debug("Transaction voided successfully, order reserved id: {$result->getQuoteReservedId()}");
                        $result->setStatus(1);
                        $result->save();
                    }
                } catch (\Acquired\Payments\Exception\Api\ApiCallException $e) {
                    $this->logger->debug("Error when trying to void {$result->getQuoteReservedId()} Reason: {$e->getMessage()}");
                }

                usleep(500000); // sleep for 0.5 seconds between each, due to API throttling
            }
        }
    }
}
