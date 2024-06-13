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

interface FilterableInterface
{
    public const FILTER = 'filter';

    public const OFFSET = 'offset';

    public const LIMIT = 'limit';

    public const COMPANY_ID = 'Company-Id';

    /**
     * Return list of possible query filter values
     *
     * @return array
     */
    public function getQueryFilters(): array;
}
