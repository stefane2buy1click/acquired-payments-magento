define([
    'underscore',
    'jquery',
    'ko',
    'uiComponent',
    'acquiredLoader',
    'mage/translate',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/storage',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/action/redirect-on-success'
], function (
    _,
    $,
    ko,
    Component,
    acquiredLoader,
    $t,
    fullScreenLoader,
    storage,
    urlBuilder,
    messageList,
    redirectOnSuccessAction,
) {

    return Component.extend({

        defaults: {
            acquired: null,
            code: 'acquired_card',
            active: false,
            nonce: null,
            acquiredComponent: null,
            sessionId: null,
            placeholder: '#acquired-payments-card-component',
            /**
             * Additional payment data
             *
             * {Object}
             */
            orderId: '',
            transactionId: '',
            timestamp: '',
            imports: {
                onActiveChange: 'active',
            },
            isPlaceOrderActionAllowed: ko,
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
         * Get payment name
         *
         * @returns {String}
         */
        getCode: function () {
            return this.code;
        },

        /**
         * Create session and initialize component
         */
        initAcquired: async function (reset = false) {

            await acquiredLoader.waitForAcquired;

            let _this = this;
            let placeholder = $(this.placeholder);

            if (reset) {
                this.nonce = this.generateNonce();
                this.acquiredComponent = null;
                placeholder.find('iframe').remove();
            }

            if (placeholder.find('iframe').length) {
                return;
            }

            try {
                const session = await this.getSession(reset);
                this._initAcquiredComponent({
                    session: session,
                    environment: _this.getMode()
                });
            } catch (error) {
                messageList.addErrorMessage({
                    message: $t('There was an issue initializing the Acquired payment method.')
                });
            }
        },

        /**
         * Initialize acquired component
         *
         * @param options
         * @private
         */
        _initAcquiredComponent: function (options) {
            this.acquiredComponent = this.acquired.components(options);
            this.acquiredComponent.create('payment', { style: this.getStyle() }).mount(this.placeholder);
        },

        /**
         * Get component mode
         *
         * @returns {*}
         */
        getMode: function () {
            return window.checkoutConfig.payment[this.getCode()].mode;
        },

        /**
         * Get custom style if set
         * @returns {*}
         */
        getStyle: function () {
            if (window.checkoutConfig.payment[this.getCode()].style) {
                try {
                    return JSON.parse(window.checkoutConfig.payment[this.getCode()].style)
                } catch (error) {
                    return null;
                }
            }

            return null;
        },

        /**
         *
         * Get 3ds challenge window size
         * @returns {String}
         */
        getTdsWindowSize: function () {
            return window.checkoutConfig.payment[this.getCode()].tds_window_size;
        },

        /**
         * Get session id
         *
         * @returns {String}
         */
        getSession: async function (createNewSession = false) {
            const sessionIdCookie = this.sessionId;

            if (!sessionIdCookie || createNewSession) {
                const acquiredGetSessionUrl = urlBuilder.createUrl('/acquired/session/' + this.nonce, {});
                const result = await storage.post(acquiredGetSessionUrl);
                this.sessionId = result.session_id;

                return result.session_id;
            } else {
                return sessionIdCookie;
            }
        },

        /**
         * Push update to acquired
         *
         * @param sessionId
         */
        updateSession: async function () {
            const sessionId = await this.getSession();
            const acquiredUpdateSessionUrl = urlBuilder.createUrl(
                '/acquired/session/update/' + this.nonce + "/" + sessionId,
                {}
            );

            await storage.post(acquiredUpdateSessionUrl, {});
        },

        /**
         * Get confirm parameters
         *
         * @returns {Promise<void>}
         */
        getConfirmParams: async function () {
            let getConfirmParamsUrl = urlBuilder.createUrl('/acquired/confirm-params/' + this.nonce, {});
            try {
                let confirmParams = await storage.post(getConfirmParamsUrl);
                if (_.indexOf(confirmParams, 0) !== -1 || confirmParams.length === 1) {
                    return confirmParams[0];
                } else {
                    return {};
                }
            } catch (error) {
                this.nonce = this.generateNonce();
                throw new Error($t('There was an issue confirming the payment.'));
            }
        },

        /**
         * Get data
         * @returns {Object}
         */
        getData: function () {
            return {
                'method': this.getCode(),
                'additional_data': {
                    'transaction_id': this.transactionId(),
                    'order_id': this.orderId(),
                    'timestamp': this.timestamp()
                }
            };
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
                await this.updateSession();
            } catch (error) {
                messageList.addErrorMessage({
                    message: error.message || $t('Payment session expired, please re-enter the payment data.')
                });
                await this.initAcquired(true);
                this.resetPlaceOrder();
                return false;
            }

            try {
                await this.formSubmitListener();

                if (key) {
                    this.getPlaceOrderDeferredObject()
                        .done(
                            function () {
                                self.afterPlaceOrder();
                                if (self.redirectAfterPlaceOrder) {
                                    redirectOnSuccessAction.execute();
                                }
                            }
                        ).fail(
                            function () {
                                self.afterPlaceOrder();
                                self.resetPlaceOrder();
                            }
                        ).always(
                            function () {
                                self.afterPlaceOrder();
                                self.isPlaceOrderActionAllowed(true);
                            }
                        );
                }
                return true;

            } catch (error) {
                messageList.addErrorMessage({
                    message: error.message || $t('An unexpected error occurred during the order placement. Please try again later.')
                });
                this.resetPlaceOrder();
                try {
                    await this.updateSession();
                } catch (error) {
                    // in case of session failure we re-initialize the form
                    await this.initAcquired(true);
                }

            } finally {
                fullScreenLoader.stopLoader();
                this.isPlaceOrderActionAllowed(true);
            }

            return false;
        },

        /**
         * Submit request to acquired
         *
         * @returns {Object}
         */
        formSubmitListener: async function () {
            try {
                const confirmParams = await this.getConfirmParams();

                let response = await this.acquired.confirmPayment(
                    {
                        components: this.acquiredComponent,
                        confirmParams: confirmParams
                    });

                // handle validation which came back from the request
                if (response.data.status === 'error') {
                    throw new Error(response.data.message);
                }

                if (response.isError()) {
                    if (response.data.status === 'declined' ||
                        response.data.status === 'blocked' ||
                        response.data.status === 'tds_failed') {

                        let message = $t('Your credit card was ' + response.data.status);

                        if (response.data.status === 'tds_failed') {
                            message = $t('Payment failed as your card is not enrolled in 3-D Secure. Please check with your bank.');
                        }

                        throw new Error(message);
                    }
                }

                if (response.isTdsPending()) {
                    await this.handleTdsChallenge(response.data.redirect_url);
                }

                if (response.isSuccess()) {
                    this.orderId(response.data.order_id);
                    this.transactionId(response.data.transaction_id);
                    this.timestamp(response.data.timestamp);
                }
            } catch (error) {
                throw new Error(error.message || $t('There was an issue submitting your payment.'));
            }
        },

        /**
         * Handles the 3-D Secure challenge by opening an iframe and waiting for a response
         *
         * @param {string} redirectUrl - The URL to be loaded in the iframe for the 3-D Secure process
         * @returns {Promise} A promise that resolves when the 3-D Secure process is completed
         */
        handleTdsChallenge: function (redirectUrl) {
            return new Promise((resolve, reject) => {
                this.openTdsIframe(redirectUrl);
                const messageHandler = (event) => {
                    if (event.data && event.data.type === 'TdsResponse') {
                        if (event.data.status === 'success') {
                            let responseData = event.data.data;
                            this.orderId(responseData.order_id);
                            this.transactionId(responseData.transaction_id);
                            this.timestamp(responseData.timestamp);

                            this.closeTdsIframe();
                            resolve();
                        } else {
                            let message = event.data.message || $t('An error occurred during 3-D Secure verification.');
                            this.closeTdsIframe();
                            reject(new Error(message));
                        }
                        window.removeEventListener('message', messageHandler, false);
                    }
                };
                window.addEventListener('message', messageHandler, false);
            });
        },

        /**
         * Opens an iframe for the 3-D Secure challenge
         *
         * @param {string} redirectUrl - The URL to be loaded in the iframe
         */
        openTdsIframe: function (redirectUrl) {
            let iframe = document.createElement('iframe');
            iframe.src = redirectUrl;
            iframe.id = 'TdsIframe';

            document.body.appendChild(iframe);
            iframe.style.height = this.getTdsWindowSize() + "px";

            this.iframe = iframe;
        },

        /**
         * Closes the 3-D Secure iframe and cleans up references to it
         */
        closeTdsIframe: function () {
            if (this.iframe) {
                this.iframe.parentNode.removeChild(this.iframe);
                this.iframe = null;
            }
        },

        /**
         * Clear session cookie
         */
        afterPlaceOrder: function () {
            // removed session id from cookie in favor of PHP session
        },

        /**
         * Reset place order
         */
        resetPlaceOrder: function () {
            fullScreenLoader.stopLoader();
            this.isPlaceOrderActionAllowed(true);
            this.nonce = this.generateNonce();
        }
    });

})