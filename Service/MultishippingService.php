<?php

declare(strict_types=1);

namespace Acquired\Payments\Service;

use Acquired\Payments\Api\Data\MultishippingInterface;
use Acquired\Payments\Model\MultishippingRepository;
use Acquired\Payments\Model\MultishippingFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

class MultishippingService
{

    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly MultishippingFactory $multishippingFactory,
        private readonly MultishippingRepository $multishippingRepository,
        private readonly SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        private readonly FilterBuilder $filterBuilder
    ) {
    }

    /**
     * Get multishipping by address id
     * @param int $addressId
     * @return MultishippingInterface|null
     */
    public function getMultishippingByAddressId(int $addressId): ?MultishippingInterface
    {
        // Get multishipping by transaction id
        $filter = $this->filterBuilder
            ->setField('quote_address_id')
            ->setValue($addressId)
            ->setConditionType('eq')
            ->create();

        $searchCriteria = $this->searchCriteriaBuilderFactory
            ->create()
            ->addFilter($filter)
            ->create();

        $multishippingResults = $this->multishippingRepository->getList($searchCriteria);

        return count($multishippingResults->getItems()) ? $multishippingResults->getItems()[0] : null;
    }

    /**
     * Get multishipping by transaction id
     * @param string $transactionId
     * @return array
     */
    public function getMultishippingByTransactionId(string $transactionId): array
    {
        // Get multishipping by transaction id
        $filter = $this->filterBuilder
            ->setField('acquired_transaction_id')
            ->setValue($transactionId)
            ->setConditionType('eq')
            ->create();

        $searchCriteria = $this->searchCriteriaBuilderFactory
            ->create()
            ->addFilter($filter)
            ->create();

        $multishippingResults = $this->multishippingRepository->getList($searchCriteria);
        return $multishippingResults->getItems();
    }

    /**
     * Get multishipping by transaction id
     * @param string $transactionId
     * @return MultishippingInterface|null
     */
    public function getMultishippingByReservedOrderId(string $transactionId): ?MultishippingInterface
    {
        // Get multishipping by transaction id
        $filter = $this->filterBuilder
            ->setField('quote_reserved_id')
            ->setValue($transactionId)
            ->setConditionType('eq')
            ->create();

        $searchCriteria = $this->searchCriteriaBuilderFactory
            ->create()
            ->addFilter($filter)
            ->create();

        $multishippingResults = $this->multishippingRepository->getList($searchCriteria);

        return count($multishippingResults->getItems()) ? $multishippingResults->getItems()[0] : null;
    }

    /**
     * Get multishipping by session id
     * @param string $sessionId
     * @return array
     */
    public function getMultishippingBySessionId(string $sessionId): array
    {
        $filter = $this->filterBuilder
            ->setField('acquired_session_id')
            ->setValue($sessionId)
            ->setConditionType('eq')
            ->create();

        $searchCriteria = $this->searchCriteriaBuilderFactory
            ->create()
            ->addFilter($filter)
            ->create();

        $multishippingResults = $this->multishippingRepository->getList($searchCriteria);

        return $multishippingResults->getItems();
    }

    /**
     * Get multishipping by order id
     * @param int $orderId
     * @return array
     */
    public function getMultishippingByOrderId(int $orderId): array
    {
        $filter = $this->filterBuilder
            ->setField('order_id')
            ->setValue($orderId)
            ->setConditionType('eq')
            ->create();

        $searchCriteria = $this->searchCriteriaBuilderFactory
            ->create()
            ->addFilter($filter)
            ->create();

        $multishippingResults = $this->multishippingRepository->getList($searchCriteria);

        return $multishippingResults->getItems();
    }

    /**
     * Get multishipping by order
     * @param int $order
     * @return MultishippingInterface|null
     */
    public function getMultishippingByOrder($order): ?MultishippingInterface
    {
        $address = $order->getIsVirtual() ? $order->getBillingAddress() : $order->getShippingAddress();
        $multishippingResult = $this->getMultishippingByAddressId((int) $address->getQuoteAddressId());

        return $multishippingResult;
    }

    /**
     * Get reserved ids by transaction id
     * @param string $orderId
     * @return array
     */
    public function getReservedIdsByTransactionId(string $orderId): array
    {
        $multishippingItems = $this->getMultishippingByTransactionId($orderId);

        $reservedIds = [];

        foreach ($multishippingItems as $multishippingItem) {
            $reservedIds[] = $multishippingItem->getQuoteReservedId();
        }

        return $reservedIds;
    }

    /**
     * Create multishipping reservations
     * @param Quote $quote
     * @param string|null $sessionId
     * @param string|null $transactionId
     * @return array
     */
    public function reserveOrderIds(Quote $quote, string $sessionId = null, string $transactionId = null) : array
    {
        $reservedIds = [];
        if ($quote->getIsMultiShipping()) {

            $shippingAddresses = $quote->getAllShippingAddresses();
            if ($quote->hasVirtualItems()) {
                $shippingAddresses[] = $quote->getBillingAddress();
            }

            foreach ($shippingAddresses as $address) {
                if($address->getAddressType() == 'shipping') {
                    $multishipping = $this->getMultishippingByAddressId( (int) $address->getId());
                    if (!$multishipping) {
                        $multishipping = $this->multishippingFactory->create();
                    }
                    if (!$multishipping->getQuoteReservedId()) {
                        $quote->setReservedOrderId(null);
                        $quote->reserveOrderId();
                        $multishipping->setQuoteReservedId($quote->getReservedOrderId());
                        $quote->setReservedOrderId(null);
                    }

                    $multishipping->setCustomerId((int) $quote->getCustomerId());
                    $multishipping->setQuoteAddressId((int) $address->getId());

                    if($sessionId) {
                        $multishipping->setAcquiredSessionId($sessionId);
                    }
                    if($transactionId) {
                        $multishipping->setAcquiredTransactionId($transactionId);
                        $multishipping->setStatus('processing');
                    }

                    $this->multishippingRepository->save($multishipping);

                    $reservedIds[] = $multishipping->getQuoteReservedId();
                }
            }
        }

        $quote->setReservedOrderId(null);
        $quote->save();

        return $reservedIds;
    }
}
