/**
 * Acquired.com Payments Integration for Magento2
 *
 * Copyright (c) 2024 Acquired Limited (https://acquired.com/)
 *
 * This file is open source under the MIT license.
 * Please see LICENSE file for more details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {

    'use strict';

    function canRenderMethod(method)
    {
        let config = window.checkoutConfig.payment;
        return config[method]
            && config[method].active
            && typeof config[method].public_key === 'string'
    }

    let acquiredCard = 'acquired_card',
        acquiredApplePay = 'acquired_applepay',
        acquiredGooglePay = 'acquired_googlepay',
        acquiredPayByBank = 'acquired_paybybank';

    if (canRenderMethod(acquiredCard)) {
        rendererList.push({
            type: acquiredCard,
            component: 'Acquired_Payments/js/view/payment/method-renderer/card'
        });
    }

    /**
     * @TODO enable each once FE READY
     *
    if (config[acquiredApplePay] && config[acquiredApplePay].isActive) {
        rendererList.push({
            type: acquiredApplePay,
            component: 'Acquired_Payments/js/view/payment/method-renderer/apple-pay'
        });
    }

    if (config[acquiredGooglePay] && config[acquiredGooglePay].isActive) {
        rendererList.push({
            type: acquiredGooglePay,
            component: 'Acquired_Payments/js/view/payment/method-renderer/google-pay'
        });
    }

    if (config[acquiredPayByBank] && config[acquiredPayByBank].isActive) {
        rendererList.push({
            type: acquiredPayByBank,
            component: 'Acquired_Payments/js/view/payment/method-renderer/pay-by-bank'
        });
    }
    */

    /** Add view logic here if needed */
    return Component.extend({});
});
