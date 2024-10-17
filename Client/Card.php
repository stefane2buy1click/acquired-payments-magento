<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Client;

class Card extends AbstractClient implements FilterableInterface
{
    use ValidateQueryParamsTrait;

    /**
     * Get Customer by ID
     *
     * @param string $customerId
     * @param array $queryParams
     * @return array|null
     * @throws \Exception
     */
    public function get(string $customerId, array $queryParams = []): ?array
    {
        self::validateQueryParams(array_keys($queryParams), $this->getQueryFilters());
        return $this->call('get', "cards/$customerId", queryParams: $queryParams);
    }

    /**
     * List all customer cards
     *
     * @param string $customerId
     * @return array|null
     * @throws \Exception
     */
    public function listAllCustomerCards(string $customerId): ?array
    {
        return $this->call('get', "customers/$customerId");
    }

    /**
     * List all cards
     *
     * @param array $queryParams
     * @return array|null
     * @throws \Exception
     */
    public function list(array $queryParams = []): ?array
    {
        self::validateQueryParams(array_keys($queryParams), $this->getQueryFilters());
        return $this->call('get', 'cards', $queryParams);
    }

    /**
     * Update Customer card
     *
     * @param string $cardId
     * @param array $payload
     * @return array|null
     * @throws \Exception
     */
    public function update(string $cardId, array $payload): ?array
    {
        return $this->call('put', "cards/$cardId", $payload);
    }

    /**
     * @inheritDoc
     */
    public function getQueryFilters(): array
    {
        return [
            self::OFFSET,
            self::LIMIT,
            self::FILTER
        ];
    }
}
