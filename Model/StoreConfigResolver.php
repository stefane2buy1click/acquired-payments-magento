<?php

/**
 *
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 *
 *
 */

namespace Acquired\Payments\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Sales\Model\OrderRepository;
use Magento\Backend\Model\Session\Quote as SessionQuote;

/**
 * @class StoreConfigResolver
 *
 * Resolves the appropriate store ID for configuration purposes.
 */
class StoreConfigResolver
{
    /**
     * @param StoreManagerInterface $storeManager
     * @param RequestHttp $request
     * @param OrderRepository $orderRepository
     * @param SessionQuote $sessionQuote
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly RequestHttp $request,
        private readonly OrderRepository $orderRepository,
        private readonly SessionQuote $sessionQuote
    ) {
    }

    /**
     * Retrieves the store ID based on the context of the request.
     *
     * @return int|null The store ID or null if it cannot be determined.
     * @throws InputException If there is an issue with input data.
     * @throws NoSuchEntityException If the order cannot be found for the provided 'order_id'.
     */
    public function getStoreId(): ?int
    {
        $currentStoreId = null;
        $currentStoreIdInAdmin = $this->sessionQuote->getStoreId();
        if (!$currentStoreIdInAdmin) {
            $currentStoreId = $this->storeManager->getStore()->getId();
        }

        $dataParams = $this->request->getParams();
        if (isset($dataParams['order_id'])) {
            try {
                $order = $this->orderRepository->get($dataParams['order_id']);
                if ($order->getEntityId()) {
                    return $order->getStoreId();
                }
            } catch (\Exception) {
                // do nothing
            }
        }

        return $currentStoreId ?: $currentStoreIdInAdmin;
    }
}
