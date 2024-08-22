<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Test\Unit\Service;

use PHPUnit\Framework\TestCase;
use Acquired\Payments\Service\GetTransactionAddressData;
use Acquired\Payments\Gateway\Config\Basic as BasicConfig;

class GetTransactionAddressDataTest extends TestCase
{
    private $getTransactionAddressData;

    protected function setUp(): void
    {
        $configMock = $this->createMock(BasicConfig::class);
        $configMock->method('shouldSendCustomerPhone')->willReturn(true);
        $this->getTransactionAddressData = new GetTransactionAddressData($configMock);
    }

    public function testGetPhoneCodeByCountryIdReturnsCorrectCode()
    {
        // Test with a known country ID
        $this->assertEquals('1', $this->getTransactionAddressData->getPhoneCodeByCountryId('US'));
        $this->assertEquals('44', $this->getTransactionAddressData->getPhoneCodeByCountryId('GB'));
        $this->assertEquals('61', $this->getTransactionAddressData->getPhoneCodeByCountryId('AU'));
    }

    public function testGetPhoneCodeByCountryIdReturnsNullForUnknownCountryId()
    {
        // Test with an unknown country ID
        $this->assertNull($this->getTransactionAddressData->getPhoneCodeByCountryId('XYZ'));
    }
}
