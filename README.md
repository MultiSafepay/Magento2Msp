<p align="center">
  <img src="https://www.multisafepay.com/img/multisafepaylogo.svg" width="400px" position="center">
</p>

# MultiSafepay plugin for Magento 2

Easily integrate MultiSafepay payment solutions into your Magento 2 webshop with the free and completely new MultiSafepay Magento 2 plugin.

[![Latest Stable Version](https://img.shields.io/packagist/v/multisafepay/magento2msp.svg)](https://packagist.org/packages/multisafepay/magento2msp)
[![Total Downloads](https://img.shields.io/packagist/dt/multisafepay/magento2msp.svg)](https://packagist.org/packages/multisafepay/magento2msp)
[![License](https://img.shields.io/packagist/l/multisafepay/magento2msp.svg)](https://github.com/MultiSafepay/Magento2Msp/blob/master/LICENSE.md)

## About MultiSafepay ##
MultiSafepay is a collecting payment service provider which means we take care of the agreements, technical details and payment collection required for each payment method. You can start selling online today and manage all your transactions from one place.
## Supported Payment Methods ##
The supported Payment Methods & Giftcards for this plugin can be found over here: [Payment Methods & Giftcards](https://docs.multisafepay.com/plugins/magento2/faq/#available-payment-methods-in-magento-2)

## Requirements
- To use the plugin you need a MultiSafepay account. You can create a test account on https://testmerchant.multisafepay.com/signup
- Magento Open Source version 2.2.x & 2.3.x

## Installation
You can install our plugin through Composer:
```shell
composer require multisafepay/magento2msp
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

For additional information or instructions please see our [installation & configuration manual](https://docs.multisafepay.com/plugins/magento2/manual/)
 
### Installation of payment link in order confirmation mail for backend orders
As of version 1.7.0 we have added a feature to include the payment link in the order confirmation mail for orders created in the backend.
This feature is customizable and can be changed to your liking.
This feature can be implemented by the following steps
1. Go to Marketing -> Email Templates
2. Add a template (import from "new order")
3. Add this *sample* code the template
    ````html
    {{depend order.getPayment().getAdditionalInformation('payment_link')}}
        <a href="{{var order.getPayment().getAdditionalInformation('payment_link')}}">Pay now with {{var order.getPayment().getAdditionalInformation('method_title')}}</a>
    {{/depend}}
    ````
4. Go to Stores -> Configuration -> Sales -> Sales Emails
5. Change the "New Order Confirmation Template" with your template

After these changes, the template should be tested to confirm it is working.

*Note*: This can also be implemented directly in the email template files.

## Support
You can create issues on our repository. If you need any additional help or support, please contact <a href="mailto:integration@multisafepay.com">integration@multisafepay.com</a>

We are also available on our Magento Slack channel [#multisafepay-payments](https://magentocommeng.slack.com/messages/multisafepay-payments/). Feel free to start a conversation or provide suggestions as to how we can refine our Magento 2 extension.

## License
[Open Software License (OSL 3.0)](https://github.com/MultiSafepay/Magento2Msp/blob/master/LICENSE.md)

## Want to be part of the team?
Are you a developer interested in working at MultiSafepay? [View](https://www.multisafepay.com/careers/#jobopenings) our job openings and feel free to get in touch with us.
