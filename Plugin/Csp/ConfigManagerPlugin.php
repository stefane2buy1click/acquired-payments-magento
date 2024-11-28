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

namespace Acquired\Payments\Plugin\Csp;

use Magento\Csp\Api\ModeConfigManagerInterface;
use Magento\Csp\Api\Data\ModeConfiguredInterface;
use Magento\Csp\Model\Mode\Data\ModeConfigured;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;

class ConfigManagerPlugin
{

    public function __construct(
        private readonly ScopeConfigInterface $config,
        private readonly Store $storeModel,
        private readonly State $state
    ) {}

    public function afterGetConfigured(
        ModeConfigManagerInterface $subject,
        ModeConfiguredInterface $result
    ) {
        if($result->isReportOnly() || !$this->overrideCspMode()) {
            return $result;
        }

        $area = $this->state->getAreaCode();
        if ($area === Area::AREA_ADMINHTML) {
            $configArea = 'admin';
        } elseif ($area === Area::AREA_FRONTEND) {
            $configArea = 'storefront';
        } else {
            throw new \RuntimeException('CSP can only be configured for storefront or admin area');
        }

        // we need to provide CSP report only as it was enforced by the payment module
        $reportUri = $this->config->getValue(
            'csp/mode/' . $configArea .'/report_uri',
            ScopeInterface::SCOPE_STORE,
            $this->storeModel->getStore()
        );

        return new ModeConfigured(true, !empty($reportUri) ? $reportUri : null);
    }

    protected function overrideCspMode() {
        return $this->config->getValue(
            'payment/acquired/configuration/csp_override',
            ScopeInterface::SCOPE_STORE,
            $this->storeModel->getStore()
        ) == 1;
    }

}