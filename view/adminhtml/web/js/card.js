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
    'uiComponent',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'Magento_Ui/js/lib/view/utils/dom-observer',
    'acquiredLoader'
], function ($, Class, alert, $t, domObserver, acquiredLoader) {
    'use strict';

    let acquired = null;

    return Class.extend({
        defaults: {
            $selector: null,
            code: 'acquired_card',
            selector: 'edit_form',
            container: 'payment_form_acquired_card',
            placeholder: '#acquired-payments-card-component',
            active: false,
            scriptLoaded: false,
            acquiredComponent: null,
            imports: {
                onActiveChange: 'active'
            }
        },

        /**
         * Set list of observable attributes
         * @returns {exports.initObservable}
         */
        initObservable: function () {
            var self = this;

            self.$selector = $('#' + self.selector);
            this._super()
                .observe([
                    'active',
                    'scriptLoaded'
                ]);

            // re-init payment method events
            self.$selector.off('changePaymentMethod.' + this.code)
                .on('changePaymentMethod.' + this.code, this.changePaymentMethod.bind(this));

            return this;
        },

        generateNonce: function () {
            const generateHash = function (input) {
                let hash = 0;
                for (let i = 0; i < input.length; i++) {
                    hash = Math.imul(31, hash) + input.charCodeAt(i) | 0;
                }
                return ('00000000' + (hash >>> 0).toString(16)).slice(-8);
            };

            return generateHash(Math.random().toString(36).substring(2) + Date.now());
        },

        /**
         * Enable/disable current payment method
         * @param {Object} event
         * @param {String} method
         * @returns {exports.changePaymentMethod}
         */
        changePaymentMethod: function (event, method) {
            this.active(method === this.code);
            return this;
        },

        /**
         * Triggered when payment changed
         * @param {Boolean} isActive
         */
        onActiveChange: function (isActive) {
            if (!isActive) {
                $(this.placeholder).hide();
                this.clearEvents();
                return;
            }

            this.disableEventListeners();

            if (typeof window.order !== 'undefined') {
                window.order.addExcludedPaymentMethod(this.code);
            }

            this._initAcquired();
            this.enableEventListeners();
            $(this.placeholder).show();
        },

        /**
         * Disable acquired payment method, reset events
         */
        clearEvents: function () {
            let self = this;
            this.$selector.off('submitOrder.acquired_card');
            this.$selector.on('submitOrder', function () {
                self.$selector.trigger('realOrder');
            });
        },

        /**
         * Disable form event listeners
         */
        disableEventListeners: function () {
            this.$selector.off('submitOrder');
            this.$selector.off('realOrder');
        },

        /**
         * Enable form event listeners
         */
        enableEventListeners: function () {
            this.$selector.on('submitOrder.acquired_card', this.submitOrder.bind(this));
        },

        /**
         * Initialize acquired payment
         *
         * @private
         */
        _initAcquired: function () {
            // generate nonce with math random and Date.now()
            this.nonce = this.generateNonce();

            try {
                if (this.error) {
                    throw new Error(this.message);
                }

                var self = this;
                acquiredLoader.waitForAcquired.then(() => {
                    if (!self.scriptLoaded()) {
                        acquired = new Acquired(self.public_key);
                        self.scriptLoaded(true);
                    }
                    self._mountAcquiredComponent();
                }).catch(function (error) {
                    throw new Error($t('An error occurred while loading the Acquired payment method: ') + error.message);
                });
            } catch (error) {
                alert({
                    content: $t('An error occurred while initializing the Acquired payment method: ') + error.message
                });
            }
        },

        _mountAcquiredComponent: function () {
            let self = this;

            const mount = function () {
                self._getSession().then(() => {
                    let iframePlaceholder = document.getElementById(self.placeholder.substring(1));
                    if (iframePlaceholder.firstChild) {
                        self.acquiredComponent = null;
                        iframePlaceholder.removeChild(iframePlaceholder.firstChild);
                    }

                    self.acquiredComponent = acquired.components({
                        session: self.session_id,
                        environment: self.mode
                    });

                    self.acquiredComponent.create('payment', { style: self.style }).mount(self.placeholder);
                });
            }

            if (!window.ACQ_GET_SESSION_URL) {
                let interval = setInterval(function () {
                    if (window.ACQ_GET_SESSION_URL) {
                        clearInterval(interval);
                        mount();
                    }
                }, 500);
            } else {
                mount();
            }

        },

        /**
         * Trigger update to acquired
         */
        _getSession: async function () {
            if (!this.nonce) {
                this.nonce = this.generateNonce();
            }

            let response = await fetch(window.ACQ_GET_SESSION_URL + "nonce?isAjax=true", {
                method: 'POST',
                body: 'form_key=' + window.FORM_KEY + '&nonce=' + this.nonce,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
            let result = await response.json();
            this.session_id = result.session_id;
        },

        /**
         * Trigger update to acquired
         */
        _updateSession: async function () {
            let response = await fetch(window.ACQ_UPDATE_SESSION_URL + "?isAjax=true", {
                method: 'POST',
                body: 'nonce=' + this.nonce + '&session_id=' + this.session_id + '&form_key=' + window.FORM_KEY,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
            let result = await response.json();
            this.session_id = result.session_id;
        },

        /**
         * Prepare for purchase
         */
        _prepareForPurchase: async function () {
            let response = await fetch(window.ACQ_CONFIRM_PURCHASE_SESSION_URL + "?isAjax=true", {
                method: 'POST',
                body: 'nonce=' + this.nonce + '&form_key=' + window.FORM_KEY,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
            let result = await response.json();
            return result;
        },

        /**
         * Submit payment to acquired and place order on magento
         */
        submitOrder: async function () {
            try {
                this.$selector.validate().form();
                this.$selector.trigger('afterValidate.beforeSubmit');
                $('body').trigger('processStop');

                // validate parent form
                if (this.$selector.validate().errorList.length) {
                    return false;
                }

                $('body').trigger('processStart');

                await this._updateSession();
                await this._prepareForPurchase();

                let response = await acquired.confirmPayment({ components: this.acquiredComponent });

                if (response.isError()) {
                    throw new Error($t('Payment failed! Reason: ') + response.data.status);
                }

                if (response.isSuccess()) {
                    $('#acquired-order-id').val(response.data.order_id);
                    $('#acquired-txn-id').val(response.data.transaction_id);
                    $('#acquired-timestamp').val(response.data.timestamp);
                    $('#' + this.selector).submit();
                }
            } catch (error) {
                $('body').trigger('processStop');
                alert({ content: error });
                this.nonce = this.generateNonce();
                try {
                    await this._updateSession();
                } catch (error) {
                    // in case of session failure we re-initialize the form
                    this._mountAcquiredComponent();
                }
            }
        },
    });
});
