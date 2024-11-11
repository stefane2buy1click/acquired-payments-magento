<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Client;

class Transaction extends AbstractClient implements FilterableInterface
{
    use ValidateQueryParamsTrait;

    /**
     * Get transaction details
     *
     * @param string $transactionId
     * @param array $queryParams
     * @return array|null
     * @throws \Exception
     */
    public function get(string $transactionId, array $queryParams = ['filter' => '']): ?array
    {
        self::validateQueryParams(array_keys($queryParams), $this->getQueryFilters());
        return $this->call('get', "transactions/$transactionId", queryParams: $queryParams);
    }

    /**
     * Refund a payment
     *
     * @param string $transactionId
     * @param array $payload
     * @return array|null
     * @throws \Exception
     */
    public function refund(string $transactionId, array $payload): ?array
    {
        return $this->call('post', "transactions/$transactionId/refund", $payload);
    }

    /**
     * Void a transaction
     *
     * @param string $transactionId
     * @return array|null
     * @throws \Exception
     */
    public function void(string $transactionId): ?array
    {
        return $this->call('post', "transactions/$transactionId/void");
    }

    /**
     * Capture payment
     *
     * @param string $transactionId
     * @param array $payload
     * @return array|null
     * @throws \Exception
     */
    public function capture(string $transactionId, array $payload): ?array
    {
        return $this->call('post', "transactions/$transactionId/capture", $payload);
    }

    /**
     * Get linked transaction
     *
     * @param string $link
     * @return array|null
     * @throws \Exception
     */
    public function getLinkedTransaction(string $link): ?array
    {
        return $this->call('get', str_replace('/v1/', '', $link));
    }

    /**
     * @inheritDoc
     */
    public function getQueryFilters(): array
    {
        return [
            self::FILTER
        ];
    }
}
