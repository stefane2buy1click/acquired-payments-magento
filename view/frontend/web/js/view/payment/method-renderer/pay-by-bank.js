/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2024 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */

define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators'
    ],
    function (
        $,
        quote,
        storage,
        customerData,
        fullScreenLoader,
        messageList,
        Component,
        placeOrderAction,
        selectPaymentMethodAction,
        customer,
        checkoutData,
        additionalValidators
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                code: 'acquired_pay_by_bank',
                active: false,
                template: 'Acquired_Payments/payment/pay-by-bank',
                orderId: '',
                transactionId: ''
            },

            /**
             * Set list of observable attributes
             *
             * @returns {exports.initObservable}
             */
            initObservable: function () {
                this._super()
                    .observe([
                        'active',
                        'orderId',
                        'transactionId',
                    ]);

                return this;
            },

            /**
             * Get payment name
             *
             * @returns {String}
             */
            getCode: function () {
                return this.code;
            },

            /**
             * Check if payment is active
             *
             * @returns {Boolean}
             */
            isActive: function () {
                let active = this.getCode() === this.isChecked();
                this.active(active);

                return active;
            },

            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }

                var self = this,
                    placeOrder;

                fullScreenLoader.startLoader();
                this.isPlaceOrderActionAllowed(false);

                try {
                    if (this.validate() && additionalValidators.validate()) {
                        placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

                        $.when(placeOrder).fail(function () {
                            self.isPlaceOrderActionAllowed(true);
                            fullScreenLoader.stopLoader();
                            messageList.addErrorMessage({
                                message: $t('An error occurred while placing the order.')
                            });
                        }).done(this.afterPlaceOrder.bind(this));
                        return true;
                    }
                    return false;
                } catch (error) {
                    fullScreenLoader.stopLoader();
                    this.isPlaceOrderActionAllowed(true);
                    messageList.addErrorMessage({
                        message: error.message || $t('An error occurred while placing the order.')
                    });
                    return false;
                }
            },

            selectPaymentMethod: function () {
                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);
                return true;
            },

            afterPlaceOrder: function (redirectUrl) {
                try {
                    if (typeof redirectUrl == 'string' && redirectUrl.length > 0 && redirectUrl.indexOf('https') === 0) {
                        window.location.replace(redirectUrl.replace(/\\/g, ""));
                    }
                } catch (e) {
                    messageList.addErrorMessage({
                        message: error.message || $t('An error occurred while redirecting to the payment gateway.')
                    });
                }
            },

        });
    }
);