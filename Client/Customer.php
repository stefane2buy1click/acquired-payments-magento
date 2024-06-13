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

namespace Acquired\Payments\Client;

class Customer extends AbstractClient implements FilterableInterface
{
    use ValidateQueryParamsTrait;

    /**
     * Create Customer
     *
     * @param array $payload
     * @return array|null
     * @throws \Exception
     */
    public function create(array $payload): ?array
    {
        return $this->call('post', 'customers', $payload);
    }

    /**
     * Get Customer by ID
     *
     * @param string $customerId
     * @return array|null
     * @throws \Exception
     */
    public function get(string $customerId): ?array
    {
        return $this->call('get', "customers/$customerId");
    }

    /**
     * Get Customers List
     *
     * @param array $queryParams
     * @return array|null
     * @throws \Exception
     */
    public function list(array $queryParams = []): ?array
    {
        self::validateQueryParams(array_keys($queryParams), $this->getQueryFilters());
        return $this->call('get', 'customers', $queryParams);
    }

    /**
     * Update Customer
     *
     * @param string $customerId
     * @param array $params
     * @return array|null
     * @throws \Exception
     */
    public function update(string $customerId, array $params): ?array
    {
        return $this->call('put', "customers/$customerId", $params);
    }

    /**
     * Fetch customer cards
     * @param string $customerId
     * @return array|null
     * @throws \Exception
     */
    public function listCards(string $customerId): ?array
    {
        return $this->call('get', "customers/$customerId/cards");
    }

    /**
     * @inheritDoc
     */
    public function getQueryFilters(): array
    {
        return [
            self::OFFSET,
            self::LIMIT,
            self::FILTER,
            self::COMPANY_ID
        ];
    }
}
