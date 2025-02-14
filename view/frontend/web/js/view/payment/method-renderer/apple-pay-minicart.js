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
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/url',
    'https://applepay.cdn-apple.com/jsapi/v1.1.0/apple-pay-sdk.js'
], function(Component, $, customerData, url) {
    'use strict';

    return Component.extend({
        defaults: {
            grandTotalAmount: 0,
            storeName: null,
            shippingMethods: null,
            selectedShippingMethod: null,
            quoteTotals: null,
            storeCountry: null,
            postalCode: null,
            supportedNetworks: null,
            storeCurrency: null,
            cartId: null,
            shippingMethods: [],
            session: null,
            totals: [],
            target: 'acquired_payment_applepay_minicart'
        },

        initObservable: function () {
            this._super().observe([
                'applePayToken'
            ]);

            return this;
        },

        initialize: function() {
            this._super();
            
            /** Check if apple pay is available on the device */
            if (window.ApplePaySession && window.ApplePaySession.canMakePayments()) {
                var applePayElement = document.getElementById(this.target);
                
                if (applePayElement) {
                    applePayElement.classList.remove('acquired-payments-hide-button');
                    applePayElement.addEventListener('click', this.createPayment.bind(this));
                }
            }         
        },

        toCamelCase: function(obj) {
            var newObj = {};
            for (let d in obj) {
                if (obj.hasOwnProperty(d)) {
                    newObj[d.replace(/(\_\w)/g, function(k) {
                        return k[1].toUpperCase();
                    })] = obj[d];
                }
            }
            return newObj;
        },

        /**
         * Get line items
         */
        getLineItems: function () {
            const totals = [...this.quoteTotals];
            totals.splice(totals.findIndex(total => total.code === 'grand_total'), 1);

            return totals;
        },

        /**
         * Get totals
         */
        getTotal: function () {
            const totals = [...this.quoteTotals];
            const total = totals.find(total => total.code === 'grand_total');
            total.label = this.storeName;

            return total;
        },

        /**
         * Handle ajax errors
         */
        handleAjaxError: function (message) {
            if (this.session) {
                try {
                    this.session.abort();
                    this.session = null;
                } catch (e) {
                    console.error('Error aborting Apple Pay session:', e);
                }
            }

            var customerMessages = customerData.get('messages')() || {},
                messages = customerMessages.messages || [];

            messages.push({
                text: message,
                type: 'error'
            });

            customerMessages.messages = messages;
            customerData.set('messages', customerMessages);

            $('[data-block="minicart"]').find('[data-role="dropdownDialog"]').dropdownDialog("close");
        },

        /**
         * Create payment
         */
        createPayment: function() {
            var request = {
                countryCode: this.storeCountry,
                currencyCode: this.storeCurrency,
                supportedNetworks: this.supportedNetworks,
                merchantCapabilities: ['supports3DS'],
                total: {
                    label: this.storeName,
                    amount: this.grandTotalAmount
                },
                shippingType: 'shipping',
                requiredBillingContactFields: [
                    'postalAddress',
                    'name',
                    'email',
                    'phone'
                ],
                requiredShippingContactFields: [
                    'postalAddress',
                    'name',
                    'email',
                    'phone'
                ]
            }

            if (!this.session) {
                this.session = new window.ApplePaySession(3, request);
            }

            //  onvalidatemerchant Call #1 to Apple
            this.session.onvalidatemerchant = async function (event) {
                try {
                    const response = await fetch(url.build('rest/V1/acquired/apple-pay/session'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            validationURL: event.validationURL
                        })
                    });
            
                    const result = await response.json();
            
                    if (result && result.merchant_session_identifier) {
                        this.session.completeMerchantValidation(this.toCamelCase(result));
                    } else {
                        this.handleAjaxError('Something went wrong, please try again.');
                    }
                } catch (error) {
                    this.handleAjaxError('Something went wrong, please try again.');
                }
            }.bind(this);

            // onpaymentmethodselected
            this.session.onpaymentmethodselected = function () {
                this.session.completePaymentMethodSelection(this.getTotal(), []);
            }.bind(this);

            // onShippingContactSelected
            this.session.onshippingcontactselected = async function (event) {
                this.countryCode = event.shippingContact.countryCode;
                this.postalCode = event.shippingContact.postalCode;
            
                try {
                    const response = await fetch(url.build('rest/V1/acquired/apple-pay/shippingmethods'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            countryCode: event.shippingContact.countryCode,
                            postalCode: event.shippingContact.postalCode
                        })
                    });

                    const result = await response.json();
            
                    this.shippingMethods = result[0].shipping_methods;
                    this.selectedShippingMethod = result[0].shipping_methods[0];
                    this.quoteTotals = result[0].totals;
            
                    this.session.completeShippingContactSelection(
                        this.session.STATUS_SUCCESS,
                        this.shippingMethods,
                        this.getTotal(),
                        this.getLineItems()
                    );
                } catch (error) {
                    this.handleAjaxError('Something went wrong, please try again.');
                }
            }.bind(this);            

            // onshippingmethodselected
            this.session.onshippingmethodselected = async function (event) {
                this.selectedShippingMethod = event.shippingMethod;
            
                try {
                    const response = await fetch(url.build('rest/V1/acquired/apple-pay/shippingmethods'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            shippingMethod: this.selectedShippingMethod,
                            countryCode: this.countryCode,
                            postalCode: this.postalCode
                        })
                    });
            
                    const result = await response.json();

                    if (!result) {
                        this.handleAjaxError('Something went wrong, please try again.');
                    }

                    this.quoteTotals = result[0].totals;
            
                    this.session.completeShippingMethodSelection(
                        this.session.STATUS_SUCCESS,
                        this.getTotal(),
                        this.getLineItems()
                    );
                } catch (error) {
                    this.handleAjaxError('Something went wrong, please try again.');
                }
            }.bind(this);

            // onpaymentauthorized - take payment here
            this.session.onpaymentauthorized = async function (event) {
                try {
                    const response = await fetch(url.build('rest/V1/acquired/apple-pay/transaction'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            shippingMethod: this.selectedShippingMethod,
                            billingAddress: event.payment.billingContact,
                            shippingAddress: event.payment.shippingContact,
                            applePayPaymentToken: event.payment.token
                        })
                    });

                    const result = await response.json();
            
                    if (!this.session) {
                        this.handleAjaxError('Payment has been cancelled.');
                        return;
                    }
            
                    if (result[0].error) {
                        this.handleAjaxError(result.error_message);
                        return;
                    }
            
                    if (!result[0].url) {
                        this.handleAjaxError('Something went wrong, please try again later.');
                        return;
                    }
            
                    this.session.completePayment(ApplePaySession.STATUS_SUCCESS);
            
                    // Invalidate customer data
                    customerData.invalidate(['cart']);
            
                    // Redirect to the result URL
                    setTimeout(() => {
                        location.href = result[0].url;
                    }, 1000);
            
                } catch (error) {
                    this.handleAjaxError('Something went wrong, please try again.');
                }
            }.bind(this);            

            this.session.oncancel = function (event) {
                this.session = null;
                this.shippingMethods = null;
                this.selectedShippingMethod = null;
                this.handleAjaxError('Payment has been cancelled.');
            }.bind(this);

            this.session.begin();
        }
    });
});
