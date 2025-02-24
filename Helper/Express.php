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

namespace Acquired\Payments\Helper;

use Exception;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Framework\App\State;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total as AddressTotal;
use Magento\Backend\Model\Session\Quote as BackendModelSession;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Directory\Model\RegionFactory;
use Acquired\Payments\Client\Payment as PaymentClient;
use Acquired\Payments\Api\Data\ApplePaySessionDataInterface;
use Acquired\Payments\Model\Api\ApplePaySession;
use Acquired\Payments\Gateway\Config\Card\Config as CardConfig;
use Acquired\Payments\Model\Api\CreateAcquiredCustomer;

class Express
{
    const CONFIG_EXPRESS_ACTIVE = 'payment/acquired_express_payments/active';
    const CONFIG_EXPRESS_APPLEPAY_ACTIVE = 'payment/acquired_express_payments/applepay_active';
    const CONFIG_EXPRESS_LOCATIONS = 'payment/acquired_express_payments/locations';

    /**
     * @param PaymentClient $paymentClient
     * @param LoggerInterface $logger
     * @param CheckoutSession $checkoutSession
     * @param BackendModelSession $backendQuoteSession
     * @param CartRepositoryInterface $cartRepository
     * @param GuestCartRepositoryInterface $guestCartRepository
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param State $state
     * @param UrlInterface $url
     * @param RequestInterface $request
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     * @param ShippingInformationInterfaceFactory $shippingInformationFactory
     * @param CountryInformationAcquirerInterface $countryInformationAcquirer
     * @param CardConfig $cardConfig
     * @param CreateAcquiredCustomer $createAcquiredCustomer
     * @param CustomerSession $customerSession
     * @param PriceCurrencyInterface $priceCurrency
     * @param QuoteManagement $quoteManagement
     * @param InvoiceService $invoiceService
     * @param OrderRepositoryInterface $orderRepository
     * @param ManagerInterface $eventManager
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param InvoiceSender $invoiceSender
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        private readonly PaymentClient $paymentClient,
        private readonly LoggerInterface $logger,
        private readonly CheckoutSession $checkoutSession,
        private readonly BackendModelSession $backendQuoteSession,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly GuestCartRepositoryInterface $guestCartRepository,
        private readonly ShippingMethodManagementInterface $shippingMethodManagement,
        private readonly State $state,
        private readonly UrlInterface $url,
        private readonly RequestInterface $request,
        private readonly ShippingInformationManagementInterface $shippingInformationManagement,
        private readonly ShippingInformationInterfaceFactory $shippingInformationFactory,
        private readonly CountryInformationAcquirerInterface $countryInformationAcquirer,
        private readonly CardConfig $cardConfig,
        private readonly CreateAcquiredCustomer $createAcquiredCustomer,
        private readonly CustomerSession $customerSession,
        private readonly PriceCurrencyInterface $priceCurrency,
        private readonly QuoteManagement $quoteManagement,
        private readonly InvoiceService $invoiceService,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ManagerInterface $eventManager,
        private readonly InvoiceRepositoryInterface $invoiceRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly InvoiceSender $invoiceSender,
        private readonly RegionFactory $regionFactory
    ) {
    }

    /**
     * Check if the payment method on specific location is active for the current store
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isExpressMethodEnabled($method = '', $location = '')
    {
        if (empty($method) || empty($location)) {
            return false;
        }

        $storeId = $this->storeManager->getStore()->getId();
        $isMethodActive = false;
        $isActive = (bool)$this->scopeConfig->getValue(self::CONFIG_EXPRESS_ACTIVE, ScopeInterface::SCOPE_STORE, $storeId);
        $locations = explode(',', (string)$this->scopeConfig->getValue(self::CONFIG_EXPRESS_LOCATIONS, ScopeInterface::SCOPE_STORE, $storeId));

        if ($method == 'applepay') {
            $isMethodActive = (bool)$this->scopeConfig->getValue(self::CONFIG_EXPRESS_APPLEPAY_ACTIVE, ScopeInterface::SCOPE_STORE, $storeId);
        }

        if ($isActive && $isMethodActive && in_array($location, $locations)) {
            return true;
        }

        return false;
    }

    /**
     * Set shipping methods
     * @param array $params
     * @return array
     */
    public function setShippingMethods(array $params)
    {
        $quote = $this->getCart();
        
        $address = $quote->getShippingAddress();
        $address->setData(null);
        $address->setCountryId($params['countryCode']);
        $address->setPostcode($params['postalCode']);
        
        if (!empty($params['countryState'])) {
            $regionId = $this->getRegionIdByCode($params['countryCode'], $params['countryState']);
            if ($regionId) {
                $address->setRegionId($regionId);
            }
        }

        if (!empty($params['shippingMethod'])) {
            $shippingMethod = explode('__SPLIT__', $params['shippingMethod']['identifier']);

            $address->setCollectShippingRates(true);
            $address->setShippingMethod($shippingMethod[0] . $shippingMethod[1]);

            $shippingInformation = $this->shippingInformationFactory->create([
                'data' => [
                    ShippingInformationInterface::SHIPPING_ADDRESS => $address,
                    ShippingInformationInterface::SHIPPING_CARRIER_CODE => $shippingMethod[0],
                    ShippingInformationInterface::SHIPPING_METHOD_CODE => $shippingMethod[1],
                ],
            ]);

            $this->shippingInformationManagement->saveAddressInformation($address->getQuoteId(), $shippingInformation);
        }
        
        // Weird bug on older devices ios 15 where onshippingmethodselected was never executed until you actually select the shipping method
        // so we are forcing the first method here if its not selected already 
        if (!$address->getShippingMethod()) {
            $methods = $this->shippingMethodManagement->getList($quote->getId());
            
            foreach ($methods as $method) {
                $address->setCollectShippingRates(true);
                $address->setShippingMethod($method->getCarrierCode() . $method->getMethodCode());
    
                $shippingInformation = $this->shippingInformationFactory->create([
                    'data' => [
                        ShippingInformationInterface::SHIPPING_ADDRESS => $address,
                        ShippingInformationInterface::SHIPPING_CARRIER_CODE => $method->getCarrierCode(),
                        ShippingInformationInterface::SHIPPING_METHOD_CODE => $method->getMethodCode(),
                    ],
                ]);
    
                $this->shippingInformationManagement->saveAddressInformation($address->getQuoteId(), $shippingInformation);
                break;
            }
        }

        $quote->setPaymentMethod('acquired_payments_express');
        $quote->getPayment()->importData(['method' => 'acquired_payments_express']);
        $this->cartRepository->save($quote);
        $quote->collectTotals();

        $methods = $this->shippingMethodManagement->getList($quote->getId());
        
        $data = [
            'shipping_methods' => !empty($methods) ? array_map(function ($method) {
                return [
                    'identifier' => $method->getCarrierCode() . '__SPLIT__' . $method->getMethodCode(),
                    'label' => $method->getMethodTitle() . ' - ' . $method->getCarrierTitle(),
                    'amount' => number_format($method->getPriceInclTax() ?: 0.0, 2, '.', ''),
                    'detail' => '',
                ];
            }, $methods) : [],
        
            'totals' => !empty($quote->getTotals()) ? array_map(function (AddressTotal $total) {
                return [
                    'type' => 'final',
                    'code' => $total->getCode(),
                    'label' => $total->getData('title'),
                    'amount' => number_format($total->getData('value') ?: 0.0, 2, '.', ''),
                ];
            }, array_values($quote->getTotals())) : []
        ];
        
        return [$data];
    }

    /**
     * Place express order
     * @param array $params
     */
    public function placeOrder(array $params)
    {
        $applePayToken = json_encode($params['applePayPaymentToken']['paymentData']);
        $quote = $this->getCart();

        $this->updateAddress($quote->getShippingAddress(), $params['shippingAddress'], $params['shippingAddress']['phoneNumber']);
        $this->updateAddress($quote->getBillingAddress(), $params['billingAddress'], $params['shippingAddress']['phoneNumber']);

        if (!$quote->getReservedOrderId()) {
            $quote->reserveOrderId();
        }

        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();

        if (!$this->customerSession->isLoggedIn()) {
            $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST)
                  ->setCustomerId(null)
                  ->setCustomerEmail($params['shippingAddress']['emailAddress'])
                  ->setCustomerFirstname($params['billingAddress']['familyName'])
                  ->setCustomerLastname($params['billingAddress']['givenName'])
                  ->setCustomerIsGuest(true)
                  ->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
        } else {
            $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER);
            $quote->setCustomerId($this->customerSession->getCustomerId());
        }

        $payload = [
            'transaction' => [
                'order_id' => $quote->getReservedOrderId(),
                'amount' => $this->priceCurrency->roundPrice($quote->getGrandTotal()),
                'currency' => strtolower($quote->getCurrency()->getStoreCurrencyCode()),
                'capture' => $this->cardConfig->getCaptureAction()
            ],
            'payment' => [
                'token' => base64_encode($applePayToken),
                'scheme' => strtolower($params['applePayPaymentToken']['paymentMethod']['network']),
                'type' => strtolower($params['applePayPaymentToken']['paymentMethod']['type']),
                'display_name' => $params['applePayPaymentToken']['paymentMethod']['displayName'],
                'create_card' => false
            ]
        ];

        if ($this->customerSession->isLoggedIn()) {
            $acquiredCustomer = $this->createAcquiredCustomer->execute($this->customerSession->getCustomerId());
            if($acquiredCustomer) {
                $payload['customer']['customer_id'] = $acquiredCustomer['customer_id'];
            }
        }

        try {
            $apiResult = $this->paymentClient->process($payload, 'apple_pay');

            if ($apiResult && isset($apiResult['status']) && in_array($apiResult['status'], ['success', 'settled', 'executed'])) {
                $this->doPlaceOrder($apiResult['transaction_id'], $quote);
            } else {
                return [
                    [
                        'error' => true,
                        'message' => 'Order could not be placed. Transaction is declined.'
                    ]
                ];
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->error(__('Error creating order: %1', $e->getMessage()));
        } catch (\Exception $e) {
            $this->logger->critical(__('Unexpected error: %1', $e->getMessage()));
        }

        return [
            [
                'url' => $this->url->getUrl('checkout/onepage/success')
            ]
        ];
    }

    /**
     * Real Place order
     *
     * @param string $transactionId
     * @param CartInterface $quote
     * @return void
     */
    public function doPlaceOrder(string $transactionId, CartInterface $quote): void
    {
        $quote->getPayment()->importData([
            'method' => 'acquired_payments_express',
            'additional_data' => [
                'transaction_id' => $transactionId,
                'payment_location' => 'mini-basket'
            ]
        ]);

        $order = $this->quoteManagement->submit($quote);

        if ($order) {
            
            $this->eventManager->dispatch(
                'checkout_type_onepage_save_order_after',
                [
                    'order' => $order,
                    'quote' => $quote
                ]
            );

            $this->checkoutSession
                ->setLastQuoteId($quote->getId())
                ->setLastSuccessQuoteId($quote->getId())
                ->setLastRealOrderId($order->getIncrementId())
                ->setLastOrderId($order->getId())
                ->setLastOrderStatus($order->getStatus());

            $this->eventManager->dispatch(
                'checkout_submit_all_after',
                [
                    'order' => $order,
                    'quote' => $quote
                ]
            );

            // Create invoice if mode = capture
            if ($order->canInvoice() && $this->cardConfig->getCaptureAction()) {
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);

                $invoice->setTransactionId($transactionId);
                $order->getPayment()->setLastTransId($transactionId);
                $order->getPayment()->setAdditionalInformation('transaction_id', $transactionId)
                                    ->setAdditionalInformation('payment_location', 'mini-basket');

                $invoice->register();
                $invoice->pay();

                $this->invoiceRepository->save($invoice);

                // Update state and status
                $state = \Magento\Sales\Model\Order::STATE_PROCESSING;
                $status = $order->getConfig()->getStateDefaultStatus($state);
                $order->setState($state)->addStatusToHistory(
                    $status,
                    "Apple Pay express payment successufully captured.",
                    false
                );

                $this->orderRepository->save($order);
                $this->invoiceSender->send($invoice);
            }
        }
    }

    /**
     * Retrieve the cart instance.
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return CartInterface
     */
    public function getCart(): CartInterface
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * Update the address details for a given address object
     * 
     * @param AddressInterface $address
     * @param array $input
     * @param string $telephone
     */
    public function updateAddress(AddressInterface $address, array $input, string $telephone = '')
    {
        $address->addData([
            AddressInterface::KEY_STREET => implode(PHP_EOL, $input['addressLines']),
            AddressInterface::KEY_COUNTRY_ID => $input['countryCode'],
            AddressInterface::KEY_LASTNAME => $input['familyName'],
            AddressInterface::KEY_FIRSTNAME => $input['givenName'],
            AddressInterface::KEY_CITY => $input['locality'],
            AddressInterface::KEY_POSTCODE => $input['postalCode'],
            AddressInterface::KEY_TELEPHONE => $telephone
        ]);

        // try to set regionId if exists
        if (array_key_exists('administrativeArea', $input)) {
            try {
                $data = $this->countryInformationAcquirer->getCountryInfo($input['countryCode']);
            } catch (NoSuchEntityException $exception) {
                $address->setRegion($input['administrativeArea']);
            }

            $regions = $data->getAvailableRegions();
            if ($regions === null) {
                $address->setRegion($input['administrativeArea']);
            } else {
                foreach ($regions as $region) {
                    if ($region->getCode() === $input['administrativeArea']) {
                        $address->setRegionId($region->getId());
                        break;
                    }
                }
            }
        }
    }

     /**
     * Fetch region ID by country code and region code
     *
     * @param string $countryCode
     * @param string $regionCode
     * @return int|null
     */
    public function getRegionIdByCode($countryCode, $regionCode)
    {
        $region = $this->regionFactory->create()->loadByCode($regionCode, $countryCode);
        return $region->getId() ?: null;
    }
}
