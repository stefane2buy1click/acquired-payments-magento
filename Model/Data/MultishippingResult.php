<?php
declare(strict_types=1);

/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

namespace Acquired\Payments\Model\Data;

use Acquired\Payments\Api\Data\MultishippingResultInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * @class MultishippingResult
 *
 * Provides a data model for multishipping processing result
 */
class MultishippingResult implements MultishippingResultInterface
{

    /**
     * @var string
     */
    private ?string $multishippingId = null;

    /**
     * @var int
     */
    private ?int $customerId = null;

    /**
     * @var OrderInterface[]
     */
    private array $candidateOrders = [];

    /**
     * @var OrderInterface[]
     */
    private array $orders = [];

    /**
     * @var float
     */
    private float $amount = 0;

    /**
     * @inheritDoc
     */
    public function getCustomerId() : ?int {
        return $this->customerId;
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId(int $customerId) : MultishippingResultInterface {
        $this->customerId = $customerId;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMultishippingOrderId(): string {
        return $this->multishippingId;
    }

    /**
     * @inheritDoc
     */
    public function setMultishippingOrderId(string $multishippingOrderId): MultishippingResultInterface {
        $this->multishippingId = $multishippingOrderId;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    /**
     * @inheritDoc
     */
    public function setOrders(array $orders): MultishippingResultInterface
    {
        $this->orders = $orders;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCandidateOrders(): array
    {
        return $this->candidateOrders;
    }

    /**
     * @inheritDoc
     */
    public function setCandidateOrders(array $orders): MultishippingResultInterface
    {
        $this->candidateOrders = $orders;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @inheritDoc
     */
    public function setAmount(float $amount): MultishippingResultInterface
    {
        $this->amount = $amount;
        return $this;
    }

}