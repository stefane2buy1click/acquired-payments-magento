/**
 * Acquired Limited Payment module (https://acquired.com/)
 *
 * Copyright (c) 2023 Acquired.com (https://acquired.com/)
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'ko',
    'mage/translate',
    'acquiredLoader',
    'Acquired_Payments/js/view/payment/method-renderer/base',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Ui/js/model/messageList',
    'mage/storage',
    'Magento_Checkout/js/model/url-builder'
], function (
    $,
    ko,
    $t,
    acquiredLoader,
    Component,
    fullScreenLoader,
    messageList,
    storage,
    urlBuilder
) {
    'use strict';

    return Component.extend({

        defaults: {
            code: 'acquired_pay_by_bank',
            template: 'Acquired_Payments/payment/multishipping/form-review',
            active: false,
            imports: {
                onActiveChange: 'active'
            },
            isPlaceOrderActionAllowed: function() {
                return true;
            }
        },

        /**
             * Initialize acquired lib
             *
             * @returns {*}
             */
        initialize: function () {
            this._super();

            var self = this;

            $('#review-button').on('click', function() {
                $('#review-button').prop('disabled', true);
                self.placeOrder();
            });

            return this;
        },

        /**
         * Action to place order
         * @param {String} key
         */
        placeOrder: async function (key) {
            let self = this;

            fullScreenLoader.startLoader();
            this.isPlaceOrderActionAllowed(false);

            try {
                await storage.post(
                    urlBuilder.createUrl('/acquired/multishipping', {}), JSON.stringify({}), true
                ).fail(
                    function () {
                        throw new Error($t('There was an issue confirming the payment.'));
                    }
                ).done(
                    function (response) {
                        $('#review-order-form').submit();
                    }
                );
                return true;

            } catch (error) {
                messageList.addErrorMessage({
                    message: error.message || $t('An unexpected error occurred during the order placement. Please try again later.')
                });
                fullScreenLoader.stopLoader();
                this.isPlaceOrderActionAllowed(true);
                return false;
            } finally {
                fullScreenLoader.stopLoader();
                this.isPlaceOrderActionAllowed(true);
            }

            return false;
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
                    'timestamp'
                ]);

            return this;
        },

        /**
         * Check if payment is active
         *
         * @returns {Boolean}
         */
        isActive: function () {
            let active = true;
            this.active(active);
            return active;
        },

        isPlaceOrderActionAllowed: function () {
            return true;
        }

    });

});