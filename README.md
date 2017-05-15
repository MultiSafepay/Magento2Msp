# Magento2Msp
MultiSafepay extension for Magento 2

The MultiSafepay extension for Magento 2 allows you to integrate add all paymentmethods and giftcards offered by MultiSafepay into your Magento 2 webshop.

The MultiSafepay extension for Magento 2 has support for:

Paymentmethods:
1. iDEAL
2. Klarna Invoice
3. Dotpay
4. EPS
5. ING Homepay *
6. Belfius *
7. AliPay *
8. KBC *
9. TrustPay *
10. PayPal
11. Pay After Delivery
12. Direct Debit
13. American Express
14. Bancontact
15. Maestro
16. Sofort
17. Banktransfer
18. Giropay
19. Mastercard
20. Visa

* These Payment methods can be used when officially released by MultiSafepay.

The following giftcards are supported:
1. Wijncadeau
2. Babygiftcard
3. Wellnessgiftcard
4. Fietsenbon
5. Beauty and Wellness
6. Webshopgiftcard
7. Parfumcadeaukaart
8. Fashioncheque
9. Erotiekbon
10. Boekenbon
11. Gezondheidsbon
12. Fashiongiftcard
13. Nationale verwencadeaubon
14. Nationale tuinbon
15. Goodcard
16. Givacard
17. Yourgift
18. Sport en Fit
19. VVV Bon
20. Podium
21. Winkel Cheque

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


