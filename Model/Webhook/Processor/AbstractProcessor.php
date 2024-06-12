<?php

/**
 * Acquired.com Payments Integration for Magento2
 *
 * Copyright (c) 2024 Acquired Limited (https://acquired.com/)
 *
 * This file is open source under the MIT license.
 * Please see LICENSE file for more details.
 */

namespace Acquired\Payments\Model\Webhook\Processor;

use Exception;
use Magento\Framework\Serialize\SerializerInterface;
use Acquired\Payments\Exception\Webhook\WebhookVersionException;
use Acquired\Payments\Exception\Webhook\WebhookIntegrityException;
use Acquired\Payments\Gateway\Config\Basic;

/**
 * @class AbstractProcessor
 *
 * Provides a foundation for processing and integrity validation of different types of webhook notifications.
 */
abstract class AbstractProcessor
{

    private const WEBHOOK_VERSION = '2';
    private const HMAC_ALGORITHM = 'sha256';
    protected const STATUS_CANCELED = 'cancelled';

    /**
     * @param Basic $basicConfig
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private readonly Basic $basicConfig,
        private readonly SerializerInterface $serializer
    ) {
    }

    /**
     * Validates the webhook data by checking its version and integrity before proceeding to the process method.
     *
     * @param array $webhookData The entire payload of the webhook data.
     * @param string $webhookHash The hash received in the webhook header for integrity validation.
     * @param string $webhookVersion The version of the webhook provided in the webhook header.
     * @return array Returns the result from the process method which handles the specific logic based on the webhook type.
     * @throws WebhookVersionException If the provided webhook version does not match the required version.
     * @throws WebhookIntegrityException If the integrity check of the webhook data fails.
     */
    final public function execute(array $webhookData, string $webhookHash, string $webhookVersion): array
    {
        if (!$this->validateWebhookVersion($webhookVersion)) {
            throw new WebhookVersionException(__('The webhook version is not appropriate. Version 2 is required.'));
        }

        if (!$this->validateIntegrity($webhookData, $webhookHash)) {
            throw new WebhookIntegrityException(__('Failed integrity check.'));
        }

        return $this->process($webhookData, $webhookHash, $webhookVersion);
    }

    /**
     * Validate the integrity of the webhook data
     *
     * @param array $webhookData The entire webhook payload data.
     * @return bool True if the integrity check passes, false otherwise.
     */
    private function validateIntegrity(array $webhookData, string $webhookHash): bool
    {
        try {
            $serializedData = $this->serializer->serialize($webhookData);
            $generatedHash = hash_hmac(self::HMAC_ALGORITHM, $serializedData, $this->basicConfig->getApiSecret());

        } catch (Exception $e) {
            return false;
        }

        return hash_equals($generatedHash, $webhookHash);
    }

    /**
     * Validates if the provided webhook version matches the expected version.
     *
     * @param string $webhookVersion The version of the webhook provided in the webhook header.
     * @return bool True if the version matches the expected version, false otherwise.
     */
    private function validateWebhookVersion(string $webhookVersion): bool
    {
        return $webhookVersion == self::WEBHOOK_VERSION;
    }

    /**
     * Process the webhook data.
     *
     * @param array $webhookData The entire payload of the webhook data.
     * @param string $webhookHash The hash received in the webhook header for integrity validation.
     * @param string $webhookVersion The version of the webhook provided in the webhook header.
     * @return array Specific response
     */
    abstract protected function process(array $webhookData, string $webhookHash, string $webhookVersion): array;

}
