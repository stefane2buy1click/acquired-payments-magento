/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'ko',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/set-payment-information',
    'Magento_Ui/js/model/messageList',
    'mage/storage',
    'Magento_Checkout/js/model/url-builder'
], function (
    $,
    ko,
    Component,
    fullScreenLoader,
    setPaymentInformationAction,
    messageList,
    storage,
    urlBuilder
) {
    'use strict';

    return Component.extend({

        defaults: {
            code: 'acquired_pay_by_bank',
            template: 'Acquired_Payments/payment/multishipping/hosted-form',
            continueSelector: '#payment-continue',
            active: false,
            imports: {
                onActiveChange: 'active'
            },
            dataSet: false
        },

        initialize: function () {
            this._super();
            $(this.continueSelector).on("click", this.onContinue.bind(this));
        },

        getData: function () {
            var data = {
                'method': this.item.method,
                'additional_data': {
                    'multishipping': true
                }
            };

            return data;
        },

        onContinue: function (e) {
            if (!this.isAcquiredMethodSelected())
                return;

            if(this.dataSet) {
                return;
            }
            var self = this;

            e.preventDefault();
            e.stopPropagation();

            self.dataSet = false;
            setPaymentInformationAction(this.messageContainer, this.getData()).then(function () {
                self.dataSet = true;
                $(self.continueSelector).trigger("click");
            }).fail(self.onSetPaymentMethodFail.bind(self));
        },

        onSetPaymentMethodFail: function () {
            this.isLoading(false);
            console.error(result);
            this.dataSet = false;
        },

        isActive: function () {
            return this.isAcquiredMethodSelected();
        },

        isAcquiredMethodSelected: function () {
            var methods = $('[name^="payment["]');

            if (methods.length === 0)
                return false;

            var acquired = methods.filter(function (index, value) {
                if (value.id == "p_method_acquired_pay_by_bank")
                    return value;
            });

            if (acquired.length == 0)
                return false;

            return acquired[0].checked;
        }

    });
});