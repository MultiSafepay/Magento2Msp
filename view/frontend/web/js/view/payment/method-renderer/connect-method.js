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
 * @copyright   Copyright (c) 2015 MultiSafepay, Inc. (http://www.multisafepay.com)
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
        ],
        function (
                $,
                Component,
                placeOrderAction,
                selectPaymentMethodAction,
                customer,
                checkoutData,
                additionalValidators,
                url) {
          var configConnect = window.checkoutConfig.payment.connect;
          'use strict';

          return Component.extend({
            defaults: {
              template: 'MultiSafepay_Connect/payment/connect',
              issuerid: '',
              creditcard: '',
              active_method: 'ideal',
            },
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
            getIssuers: function () {
              return configConnect.issuers;
            },
            getCreditcards: function () {
              return configConnect.creditcards;
            }
          });
        }
);