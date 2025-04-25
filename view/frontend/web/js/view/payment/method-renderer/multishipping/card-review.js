/**
 * Acquired.com Payments Integration for Magento2
 *
 * Copyright (c) 2024 Acquired Limited (https://acquired.com/)
 *
 * This file is open source under the MIT license.
 * Please see LICENSE file for more details.
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
            code: 'acquired_card',
            template: 'Acquired_Payments/payment/multishipping/form-review',
            active: false,
            imports: {
                onActiveChange: 'active'
            },
            isPlaceOrderActionAllowed: function () {
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
            // generate nonce with math random and Date.now()
            this.nonce = this.generateNonce();

            var self = this;

            acquiredLoader.waitForAcquired.then(function () {
                self.acquired = new Acquired(window.checkoutConfig.payment[self.getCode()].public_key);
                self.initAcquired();

                $('#review-button').on('click', function () {
                    $('#review-button').prop('disabled', true);
                    self.placeOrder().then(function (result) {
                        if (result) {
                            fullScreenLoader.stopLoader();

                            self.updateMultishippingData(function (response) {
                                if (!response) {
                                    $('#review-button').prop('disabled', false);
                                } else {
                                    $('#review-order-form').submit();
                                }
                            });
                        } else {
                            $('#review-button').prop('disabled', false);
                            messageList.addErrorMessage({
                                message: $t('An unexpected error occurred during the order placement. Please try again later.')
                            });
                        }
                    }).catch(function (error) {
                        $('#review-button').prop('disabled', false);
                        messageList.addErrorMessage({
                            message: error.message || $t('An unexpected error occurred during the order placement. Please try again later.')
                        });
                    });
                });
            });

            return this;
        },

        updateMultishippingData: function (callback) {
            var self = this;
            var serviceUrl = '/' + urlBuilder.createUrl('/acquired/multishipping', {});

            $.ajax({
                url: serviceUrl,
                type: 'POST',
                data: JSON.stringify({
                    sessionId: self.sessionId,
                    transactionId: self.transactionId(),
                    orderId: self.orderId(),
                    timestamp: self.timestamp(),
                    hash: self.hash(),
                }),
                dataType: 'json',
                global: true,
                contentType: 'application/json',
            }).always(function (result) {
                callback(result);
            });
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
                    'timestamp',
                    'hash'
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