<?php declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Block\Multishipping;

use Magento\Framework\View\Element\Template;

class Overview extends Template
{

    /** @var \Acquired\Payments\Helper\Multishipping */
    private $helper;

    /**
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     */
    protected $configProvider;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param \Acquired\Payments\Helper\Multishipping $helper
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Acquired\Payments\Helper\Multishipping $helper,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->configProvider = $configProvider;
        $this->serializer = $serializer;
    }



    public function willPayWithAcquired()
    {
        $quote = $this->helper->getQuote();
        $paymentMethod = $quote->getPayment()->getMethod();
        return (strpos($paymentMethod, "acquired_") === 0);
    }

    public function getPaymentMethod() {
        $quote = $this->helper->getQuote();
        return $quote->getPayment()->getMethod();
    }

    public function getStoreCode()
    {
        $store = $this->helper->getCurrentStore();
        return $store->getCode();
    }

    public function getParams()
    {
        return [];
    }

    public function hasPaymentMethod()
    {

        if (!$this->willPayWithAcquired())
        {
            return "false";
        }

        return "true";
    }

    /**
     * Retrieve checkout configuration
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getCheckoutConfig()
    {
        return $this->configProvider->getConfig();
    }

    /**
     * Retrieve serialized checkout config.
     *
     * @return bool|string
     * @since 100.2.0
     */
    public function getSerializedCheckoutConfig()
    {
        return  $this->serializer->serialize($this->getCheckoutConfig());
    }

}