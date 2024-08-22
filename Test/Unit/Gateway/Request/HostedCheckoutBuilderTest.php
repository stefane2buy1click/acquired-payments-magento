<?php

declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Test\Unit\Gateway\Request;

use PHPUnit\Framework\TestCase;
use Acquired\Payments\Gateway\Request\HostedCheckoutBuilder;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Acquired\Payments\Gateway\Config\Hosted\Config as HostedConfig;
use Acquired\Payments\Model\Api\CreateAcquiredCustomer;
use Acquired\Payments\Service\GetTransactionAddressData;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Quote\Model\Quote;
use Acquired\Payments\Exception\Command\BuilderException;
use Magento\Store\Model\Store;

class HostedCheckoutBuilderTest extends TestCase
{
    private $logger;
    private $storeMock;
    private $storeManager;
    private $urlBuilder;
    private $hostedConfig;
    private $createAcquiredCustomer;
    private $getTransactionAddressData;
    private $quoteRepository;
    private $hostedCheckoutBuilder;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->storeMock = $this->createMock(Store::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->urlBuilder = $this->createMock(UrlInterface::class);
        $this->hostedConfig = $this->createMock(HostedConfig::class);
        $this->createAcquiredCustomer = $this->createMock(CreateAcquiredCustomer::class);
        $this->getTransactionAddressData = $this->createMock(GetTransactionAddressData::class);
        $this->quoteRepository = $this->createMock(CartRepositoryInterface::class);

        $this->storeManager->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->method('getCurrentCurrencyCode')->willReturn('GBP');

        $this->hostedCheckoutBuilder = new HostedCheckoutBuilder(
            $this->logger,
            $this->storeManager,
            $this->urlBuilder,
            $this->hostedConfig,
            $this->createAcquiredCustomer,
            $this->getTransactionAddressData,
            $this->quoteRepository
        );
    }

    public function testBuildWithValidData()
    {
        $buildSubject = [
            'payment' => $this->createPaymentMock(),
            'amount' => 100.00
        ];

        $this->urlBuilder->method('getUrl')
            ->willReturn('https://example.com/redirect', 'https://example.com/webhook');

        $result = $this->hostedCheckoutBuilder->build($buildSubject);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('transaction', $result);
        $this->assertArrayHasKey('redirect_url', $result);
        $this->assertArrayHasKey('webhook_url', $result);
    }

    public function testBuildWithMissingAmount()
    {
        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('Amount should be provided');

        $buildSubject = [
            'payment' => $this->createPaymentMock()
        ];

        $this->hostedCheckoutBuilder->build($buildSubject);

    }

    public function testBuildWithInvalidRedirectUrl()
    {
        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('Redirect URL must be HTTPS:');

        $buildSubject = [
            'payment' => $this->createPaymentMock(),
            'amount' => 100.00
        ];

        $this->urlBuilder->method('getUrl')
            ->willReturn('http://example.com/redirect');

        $this->hostedCheckoutBuilder->build($buildSubject);
    }

    public function testBuildWithInvalidWebhookUrl()
    {
        $this->expectException(BuilderException::class);
        $this->expectExceptionMessage('Webhook URL must be HTTPS:');

        $buildSubject = [
            'payment' => $this->createPaymentMock(),
            'amount' => 100.00
        ];

        $this->urlBuilder->method('getUrl')
            ->willReturn('https://example.com/redirect', 'http://example.com/webhook');

        $this->hostedCheckoutBuilder->build($buildSubject);
    }

    private function createPaymentMock()
    {
        $paymentMock = $this->createMock(Payment::class);
        $orderMock = $this->createMock(Order::class);
        $quoteMock = $this->createMock(Quote::class);

        $paymentMock->method('getOrder')->willReturn($orderMock);
        $orderMock->method('getQuoteId')->willReturn('123');
        $this->quoteRepository->method('get')->willReturn($quoteMock);

        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $paymentDO->method('getPayment')->willReturn($paymentMock);

        return SubjectReader::readPayment(['payment' => $paymentDO]);
    }
}
