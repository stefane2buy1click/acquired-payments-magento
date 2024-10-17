<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Api\Data;

use Magento\Sales\Api\Data\OrderInterface;

interface MultishippingResultInterface
{

    /**
     * Returns the customer ID
     *
     * @return int|null
     */
    public function getCustomerId() : ?int;

    /**
     * Sets the customer ID
     *
     * @param int $customerId
     * @return MultishippingResultInterface
     */
    public function setCustomerId(int $customerId) : MultishippingResultInterface;

    /**
     * Returns the multishipping order ID
     *
     * @return string
     */
    public function getMultishippingOrderId(): string;

    /**
     * Sets the multishipping order ID
     *
     * @param string $multishippingOrderId
     * @return MultishippingResultInterface
     */
    public function setMultishippingOrderId(string $multishippingOrderId): MultishippingResultInterface;

    /**
     * Returns resolved orders for multishipping processing
     *
     * @return OrderInterface[]
     */
    public function getOrders() : array;

    /**
     * Set resolved orders for multishipping processing
     *
     * @param OrderInterface[] $orders
     * @return MultishippingProcessingResultInterface
     */
    public function setOrders(array $orders) : MultishippingResultInterface;

    /**
     * Returns initial orders set for multishipping processing
     *
     * @return OrderInterface[]
     */
    public function getCandidateOrders() : array;

    /**
     * Sets initial orders for multishipping processing
     *
     * @param OrderInterface[] $orders
     * @return MultishippingProcessingResultInterface
     */
    public function setCandidateOrders(array $orders) : MultishippingResultInterface;

    /**
     * Returns amount for multishipping processing
     *
     * @return float
     */
    public function getAmount(): float;

    /**
     * Set amount for multishipping processing
     *
     * @param float $amount
     * @return MultishippingProcessingResultInterface
     */
    public function setAmount(float $amount): MultishippingResultInterface;

}