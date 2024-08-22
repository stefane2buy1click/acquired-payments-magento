<?php

/**
 *
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 *
 *
 */

namespace Acquired\Payments\Model;

use Exception;
use Psr\Log\LoggerInterface;
use Acquired\Payments\Gateway\Config\Basic;

/**
 * @class TdsResponseHandler
 *
 * Handles processing and validation of 3-D Secure transaction responses.
 * Ensures data integrity and determines transaction status.
 */
class TdsResponseHandler
{

    private const ALGORITHM_KEY = 'sha256';
    private const STATUS_ERROR = 'error';
    private const STATUS_SUCCESS = 'success';
    private const RESPONSE_TYPE = 'TdsResponse';
    private const STATUS_KEY = 'status';
    private const TRANSACTION_ID_KEY = 'transaction_id';
    private const ORDER_ID_KEY = 'order_id';
    private const TIMESTAMP_KEY = 'timestamp';
    private const HASH_KEY = 'hash';
    private const REASON_KEY = 'reason';


    /**
     * @param Basic $basicConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Basic $basicConfig,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Processes the 3-D Secure response.
     *
     * @param array $postData The post data received from the 3-D Secure process.
     * @return array Processed response including status, message, and original data.
     */
    public function processResponse(array $postData): array
    {
        try {
            [$status, $message] = $this->determineStatusAndMessage($postData);

        } catch (Exception $e) {
            $status = self::STATUS_ERROR;
            $message = __('An error occurred while processing the 3-D Secure response.');

            $this->logger->critical(__('Error processing 3DS: %1', $e->getMessage()), ['exception' => $e]);
        }

        return [
            'type' => self::RESPONSE_TYPE,
            'status' => $status,
            'message' => $message,
            'data' => $postData
        ];
    }

    /**
     * Validates the integrity of the data response comparing hash values.
     *
     * This function ensures data authenticity based on the hash comparison.
     * See [Acquired Documentation](https://docs.acquired.com/docs/3d-secure#step-4-validate-the-integrity-of-the-form-data)
     *
     * @param array $postData The post data to validate.
     * @return bool True if the data response is valid, false otherwise.
     */
    private function validateResponseIntegrity(array $postData): bool
    {
        $concatenatedParams = implode('', [
            $postData[self::STATUS_KEY],
            $postData[self::TRANSACTION_ID_KEY],
            $postData[self::ORDER_ID_KEY],
            $postData[self::TIMESTAMP_KEY]
        ]);

        $paramsHash = hash(self::ALGORITHM_KEY, $concatenatedParams);
        $generatedHash = hash(self::ALGORITHM_KEY, $paramsHash . $this->basicConfig->getApiSecret());

        return $generatedHash === $postData[self::HASH_KEY];
    }


    /**
     * Determines the status and message based on the 3-D Secure response.
     *
     * @param array $response The response data to evaluate.
     * @return array An array containing the status and message.
     */
    private function determineStatusAndMessage(array $response): array
    {
        if (!$this->validateResponseIntegrity($response)) {
            return [self::STATUS_ERROR, __('Invalid 3-D Secure data integrity!')];
        }

        $status = $response[self::STATUS_KEY] === self::STATUS_SUCCESS ? self::STATUS_SUCCESS : self::STATUS_ERROR;
        $message = $status === self::STATUS_SUCCESS ?
            __('3-D Secure verification completed') :
            __('3-D Secure verification failed. Reason: %1', $response[self::REASON_KEY]);

        return [$status, $message];
    }
}
