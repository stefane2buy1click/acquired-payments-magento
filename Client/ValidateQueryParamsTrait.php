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

use Acquired\Payments\Exception\Api\ValidationException;

trait ValidateQueryParamsTrait
{

    /**
     * Validate query parameters
     *
     * @param array $params
     * @param array $queryFilters
     * @return void
     * @throws ValidationException
     */
    public function validateQueryParams(array $params, array $queryFilters): void
    {
        foreach ($params as $param) {
            if (!in_array($param, $queryFilters)) {
                throw new ValidationException(__('Param %1 is not allowed for filtering', $param));
            }
        }
    }
}
