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
 * @author      Ruud Jonk <techsupport@multisafepay.com>
 * @copyright   Copyright (c) 2018 MultiSafepay, Inc. (https://www.multisafepay.com)
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
        'jquery/ui',
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
        ui,
        modal,
        ko
    ) {
        var configConnect = window.checkoutConfig.payment.connect;
        'use strict';

        return Component.extend({
            defaults: {
                template: 'MultiSafepay_Connect/payment/connect',
                issuerid: '',
                creditcard: '',
                active_method: 'ideal',
                recurring: '',
                showInput: false
            },
            useCustomName: ko.observable(false),
            initialize: function () {

                this._super();
                if (configConnect.active_method == this.item.method) {
                    this.selectPaymentMethod(configConnect.active_method)
                }
                return this;
            },
            initObservable: function () {
                this._super()
                    .observe('issuerid');
                this._super()
                    .observe('creditcard');
                return this;
            },
            getData: function () {
                return {
                    "method": this.item.method,
                    "additional_data": {
                        'issuerid': this.issuerid(),
                        'creditcard': this.creditcard()
                    }
                };
            },
            /** Returns send check to info */
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
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
                if (this.item.method == 'ideal') {
                    window.location.replace(url.build('multisafepay/connect/redirect/?issuer=' + $('[name="issuerid"]').val()));
                } else if (this.item.method == 'creditcard') {
                    window.location.replace(url.build('multisafepay/connect/redirect/?creditcard=' + $('[name="creditcard"]').val()));
                } else if (this.item.method == 'visa' || this.item.method == 'mastercard' || this.item.method == 'americanexpress') {
                    var active = this.item.method;
                    var hash = $('[name="recurring"][data-type="' + active + '"]').val();
                    var name = $('[name="custom_name"][data-type="' + active + '"]').val();
                    var save = this.useCustomName._latestValue;
                    if (hash === undefined) {
                        hash = "";
                    }
                    if (name === undefined) {
                        name = "";
                    }
                    window.location.replace(url.build('multisafepay/connect/redirect/?recurring_hash=' + hash + '&name=' + name + '&save=' + save));
                } else {
                    window.location.replace(url.build('multisafepay/connect/redirect/'));
                }

            },
            getGatewayImage: function () {
                return configConnect.images[this.item.method];
            },
            showIssuers: function () {
                if (this.item.method == 'ideal') {
                    return true;
                } else {
                    return false;
                }
            },
            showCards: function () {
                if (this.item.method == 'creditcard') {
                    return true;
                } else {
                    return false;
                }
            },
            showRecurring: function () {
                if (configConnect.recurrings.enabled) {
                    if (this.item.method == 'visa' || this.item.method == 'mastercard' || this.item.method == 'americanexpress') {
                        var active = this.item.method;
                        if (configConnect.recurrings[active].hasRecurrings) {

                            return true;
                        }
                    }
                }
                return false;

            },
            showAddRecurringData: function () {
                if (configConnect.recurrings.enabled && customer.isLoggedIn()) {
                    if (this.item.method == 'visa' || this.item.method == 'mastercard' || this.item.method == 'americanexpress') {
                        return true;
                    }
                }
                return false;
            },
            deleteRecurring: function () {
                var $self = this;
                if (confirm($.mage.__('Are you sure you want to delete this creditcard?'))) {
                    var active = this.item.method;
                    var target = $("select[name='recurring'][data-type='"+ active + "']");
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
                            $self.recurringMethods[active].remove(function (item){
                                return item.value === value
                            });
                            $self.selectedRecurring(null);
                        }
                    })
                }
            },
            selectedRecurring: ko.observable(),
            recurringMethods: Array(),
            clearSelectedRecurring: function(){
                $("select[name='recurring']").val("");
                this.useCustomName(false);
                return this.selectedRecurring(null);
            },
            setSelectedRecurring: function(data, event){
                var target = event.currentTarget;
                var value = $(target).val();
                return this.selectedRecurring(value)
            },

            getIssuers: function () {
                return configConnect.issuers;
            },
            getCreditcards: function () {
                return configConnect.creditcards;
            },
            getRecurrings: function () {
                var active = this.item.method;
                this.recurringMethods[active] = ko.observableArray(configConnect.recurrings[active].recurrings);
                return ko.observableArray(configConnect.recurrings[active].recurrings);
            },
            showRecurringModal: function () {
                $('<div />').html('<ol>' +
                    '<li><i class="fa fa-lock"></i> <strong>'+ $.mage.__('Guarantees from MultiSafepay') +'</strong><br>' +
                    $.mage.__("Your creditcard credentials will be saved in our secure bankserver. The webshop has for your safety no access to this information and will not be saved in any way") +
                    '<li><i class="fa fa-check-square-o"></i> <strong>'+ $.mage.__("Fast and easy") +'</strong><br>' +
                    $.mage.__("By registering your credentials, You can speed up your purchases. Because you don't need to fill in your credentials again") +
                    '<li><i class="fa fa-check-square-o"></i> <strong>'+ $.mage.__("Free of charge") +'</strong><br>' +
                    $.mage.__("Activating and using this function is free of charge, and you can disable it any time")+
                    '</ol>')
                    .modal({
                        title: '',
                        autoOpen: true,
                        closed: function () {
                            // on close
                        },
                        buttons: [{
                            text: 'Confirm',
                            attr: {
                                'data-action': 'confirm'
                            },
                            'class': 'action-primary',
                        }]
                    });
            },


        });
    }
);