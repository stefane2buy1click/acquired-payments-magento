/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */
define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {

    'use strict';

    function canRenderMethod(method) {
        let config = window.checkoutConfig.payment;
        return config[method]
            && config[method].active
            && typeof config[method].public_key === 'string'
    }

    let acquiredCard = 'acquired_card',
        acquiredPayByBank = 'acquired_pay_by_bank';

    if (canRenderMethod(acquiredCard)) {
        rendererList.push({
            type: acquiredCard,
            component: 'Acquired_Payments/js/view/payment/method-renderer/card'
        });
    }

    if (canRenderMethod(acquiredPayByBank)) {
        rendererList.push({
            type: acquiredPayByBank,
            component: 'Acquired_Payments/js/view/payment/method-renderer/pay-by-bank'
        });
    }

    /** Add view logic here if needed */
    return Component.extend({});
});
