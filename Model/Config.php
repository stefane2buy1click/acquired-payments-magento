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

use Magento\Framework\App\ProductMetadataInterface;

class Config
{
    public static $version = "1.0.0-beta.4";

    public function __construct(
        private readonly ProductMetadataInterface $productMetadata
    ) {
    }

    public function getClientVersion()
    {
        return 'M: ' . $this->productMetadata->getEdition() . 'A:' . self::$version;
    }
}