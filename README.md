# Magento2Msp
MultiSafepay extension for Magento 2

The MultiSafepay extension for Magento 2 allows you to integrate add all paymentmethods and giftcards offered by MultiSafepay into your Magento 2 webshop.

The MultiSafepay extension for Magento 2 has support for:

Paymentmethods:
American Express
Bancontact
Banktransfer
Pay After Delivery
Direct Debit
Dotpay
E-invoice
EPS
Ferbuy
Giropay
iDEAL
Klarna Invoice
Maestro
Mastercard
PayPal
Sofort
Visa

The following giftcards are supported:
Babygiftcard
Beauty and Wellness
Boekenbon
Erotiekbon
Fashioncheque
Fashiongiftcard
Fietsenbon
Gezondheidsbon
Goodcard
Nationaletuinbon
Nationaleverwencadeaubon
Parfum cadeaukaart
Podium
Sport en Fit
VVV bon
Webshopgiftcard
Wellnessgiftcard
Wijncadeau
Winkel cheque
Yourgift

Next to the above it has support for
1. Multistore setup
2. Magestore one-step-checkout
3. Magelaza one-step-checkout
4. Refunds from within Magento 
5. Auto update transaction to shipped for Pay After Delivery, Klarna and E-invoice
6. Cart is active on cancelled transaction
7. iDEAL issuer selection within Magento
8. Creditcard gateway, this one is optional and can be used to offer a grouped CreditCard payment method with a card dropdown.
 
Your notifcation url can be set to the following: (within your MultiSafepay website profile)
Set [yoursiteurl]/multisafepay/connect/notification as Notification URL in the MSP merchant center
If the url is not configured then the notification_url added to the transaction request will be used to process the callback.


