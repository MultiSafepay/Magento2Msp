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
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'Magento_Ui/js/modal/modal',
        'ko',
        'mage/translate'
    ],
    function (
        $,
        Component,
        placeOrderAction,
        selectPaymentMethodAction,
        customer,
        checkoutData,
        additionalValidators,
        url,
        modal,
        ko
    ) {
        var configConnect = window.checkoutConfig.payment.connect;
        'use strict';

        return Component.extend({
            defaults: {
                template: 'MultiSafepay_Connect/payment/creditcard',
                active_method: 'creditcard',
                supportedTokenizationGateways: ['americanexpress','mastercard','visa']
            },
            showSaveToken: ko.observable(true),
            showCustomName: ko.observable(false),
            initialize: function () {
                this._super();
                if (configConnect.active_method == this.item.method) {
                    this.selectPaymentMethod(configConnect.active_method)
                }
                return this;
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
            setActiveToken: function(data, event){
                var creditcards = ['VISA','AMEX','MASTERCARD']; //MAESTRO not included because it has no tokenization support

                var target = event.currentTarget;
                var value = $(target).val();

                //Check if save data should be shown
                this.showSaveTokenCheckbox();

                if($(target).attr('id') === 'creditcard_expiration') {
                    if (creditcards.includes(value)) {
                        return this.selectedToken(false);
                    }
                }

                return this.selectedToken(value)
            },
            afterPlaceOrder: function () {
                var active = this.item.method;
                var token = $('[name="token"][data-type="' + active + '"]');
                var nameField = $('[name="custom_name"][data-type="' + active + '"]');
                var hash = (typeof token.val() === 'undefined') ? "" : token.val();
                var name = (typeof nameField.val() === 'undefined') ? "" : nameField.val();
                var save = $('[name="saveCreditCard"][data-type="' + active + '"]:checked').length > 0;

                if (hash !== "") {
                    window.location.replace(url.build('multisafepay/connect/redirect/?recurring_hash=' + hash));
                    return;
                }

                if (save) {
                    window.location.replace(url.build('multisafepay/connect/redirect/?name=' + name + '&save=' + save));
                    return;
                }
                window.location.replace(url.build('multisafepay/connect/redirect/'));
            },
            getGatewayImage: function () {
                return configConnect.images[this.item.method];
            },
            tokenizationEnabled: function () {
                return configConnect.recurrings.enabled && customer.isLoggedIn();
            },
            selectedToken: ko.observable(),
            getTokens: function () {
                var tokens = ko.observableArray();
                var $self = this;

                $self.supportedTokenizationGateways.forEach(function (gateway) {
                    configConnect.recurrings[gateway].recurrings.forEach(function (token) {
                        tokens.push(token);
                    })
                });

                return tokens;
            },
            deleteToken: function () {
                var $self = this;
                if (confirm($.mage.__('Are you sure you want to delete this creditcard?'))) {
                    var active = this.item.method;
                    var target = $("select[name='token'][data-type='"+ active + "']");
                    if(target.length < 1){
                        target = $("#creditcard_expiration");
                    }
                    var value = $(target).val();

                    //AJAX CALL
                    $.ajax({
                        url: url.build('multisafepay/connect/notification/'),
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            hash: value
                        },
                        complete: function(response){
                            $(target).find("option:selected").remove();
                            $self.selectedToken(false);
                            $self.showSaveTokenCheckbox();
                        }
                    })
                }
            },
            showSaveTokenCheckbox: function () {
                var active = this.item.method;
                var value = $('[name="token"][data-type="' + active + '"]').val();

                if (value !== "") {
                    this.showSaveToken(false);
                    return;

                }
                this.showSaveToken(true);
            },
            clearSelectedToken: function () {
                $("select[name='token']").val("");
                this.showSaveTokenCheckbox();
                return this.selectedToken(null);
            },
            hasTokens: function () {
                var tokens = this.getTokens();
                return tokens._latestValue.length > 0
            }
        });
    }
);
