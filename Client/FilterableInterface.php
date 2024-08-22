<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
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
