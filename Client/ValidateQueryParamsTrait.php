<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
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
