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

namespace Acquired\Payments\Model\Webhook;

use InvalidArgumentException;
use Acquired\Payments\Model\Webhook\Processor\CompositeStatusUpdateProcessorFactory;
use Acquired\Payments\Model\Webhook\Processor\UnsupportedTypeProcessorFactory;

/**
 * @class ProcessorFactory
 *
 * Dynamically instantiates webhook processors based on the type of webhook notification.
 */
class ProcessorFactory
{

    /**
     * @param CompositeStatusUpdateProcessorFactory $statusUpdateProcessorFactory
     * @param UnsupportedTypeProcessorFactory $unsupportedTypeProcessorFactory
     */
    public function __construct(
        private readonly CompositeStatusUpdateProcessorFactory $statusUpdateProcessorFactory,
        private readonly UnsupportedTypeProcessorFactory $unsupportedTypeProcessorFactory
    ) {
    }

    /**
     * Creates an instance of a webhook processor based on the specified type.
     *
     * @param string $webhookType The type of the webhook notification to process.
     * @return mixed An instance of the specific webhook processor.
     * @throws InvalidArgumentException If an unknown processor type is provided.
     */
    public function create(string $webhookType): mixed
    {
        return match ($webhookType) {
            'status_update' => $this->statusUpdateProcessorFactory->create(),
             default => $this->unsupportedTypeProcessorFactory->create()
        };
    }
}
