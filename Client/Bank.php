<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Client;

use Magento\Framework\Exception\LocalizedException;

class Bank extends AbstractClient implements FilterableInterface
{
    use ValidateQueryParamsTrait;

    /**
     * Get list of all supported banks
     *
     * @param array $queryParams
     * @return array|null
     * @throws LocalizedException
     * @throws \Exception
     */
    public function list(array $queryParams = []): ?array
    {
        self::validateQueryParams(array_keys($queryParams), $this->getQueryFilters());
        return $this->call('get', 'aspsps', queryParams: $queryParams);
    }

    /**
     * Process a pay by bank
     *
     * @param array $payload
     * @return array|null
     * @throws \Exception
     */
    public function process(array $payload): ?array
    {
        return $this->call('post', 'single-immediate-payment', $payload);
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
