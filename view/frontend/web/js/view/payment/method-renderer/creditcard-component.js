/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is provided with Magento in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the MultiSafepay plugin
 * to newer versions in the future. If you wish to customize the plugin for your
 * needs please document your changes and make backups before your update.
 *
 * @category    MultiSafepay
 * @package     Connect
 * @author      MultiSafepay <integration@multisafepay.com>
 * @copyright   Copyright (c) MultiSafepay, Inc. (https://www.multisafepay.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
 * ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/storage',
        'mage/url',
        'creditcardComponent',
        'mspcrypt',
    ],
    function (
        $,
        Component,
        placeOrderAction,
        selectPaymentMethodAction,
        customer,
        quote,
        urlBuilder,
        checkoutData,
        additionalValidators,
        storage,
        url,
    ) {
        var configConnect = window.checkoutConfig.payment.connect;
        'use strict';

        return Component.extend({
            msp: null,
            defaults: {
                template: 'MultiSafepay_Connect/payment/creditcard-component',
                active_method: 'creditcard',
            },
            initialize: function () {
                this._super();
                if (configConnect.active_method == this.item.method) {
                    this.selectPaymentMethod(configConnect.active_method)
                }
                this.constructCreditcardComponent();

                return this;
            },
            constructCreditcardComponent: function () {
                this.msp = new MultiSafepay({
                    env: configConnect.environment,
                    apiToken: configConnect.apitoken,
                    // envApiEndpoint: 'https://devapi.multisafepay.com/v1/',
                    order: {
                        customer: {
                            country: quote.billingAddress().countryId,
                            locale: configConnect.locale,
                        },
                        currency: quote.totals()['base_currency_code'],
                        amount: quote.totals()['base_grand_total'] * 100,
                        template: {
                            settings: {
                                embed_mode: false
                            }
                        }
                    }
                });
            },
            initCreditcardComponent: function () {
                this.msp.init('payment', {
                    container: '#MSPPayment',
                    gateway: 'CREDITCARD'
                });
            },
            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }

                var self = this,
                    placeOrder,
                    emailValidationResult = customer.isLoggedIn(),
                    loginFormSelector = 'form[data-role=email-with-possible-login]';
                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }
                if (emailValidationResult && this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);
                    placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

                    $.when(placeOrder).fail(function () {
                        self.isPlaceOrderActionAllowed(true);
                    }).done(this.afterPlaceOrder.bind(this));
                    return true;
                }
                return false;
            },
            selectPaymentMethod: function () {
                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);
                return true;
            },
            afterPlaceOrder: function () {
                let errors = this.msp.getErrors().count > 0;
                let payload = this.msp.getPaymentData().payment_data.payload;

                if (payload !== "" && !errors) {
                    window.location.replace(url.build('multisafepay/connect/redirect/?payload=' + payload));
                    return;
                }

                window.location.replace(url.build('multisafepay/connect/redirect/'));
            },
            getGatewayImage: function () {
                return configConnect.images[this.item.method];
            },
        });
    }
);
