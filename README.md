# Magento2Msp
MultiSafepay extension for Magento 2

The MultiSafepay extension for Magento 2 allows you to integrate add all paymentmethods and giftcards offered by MultiSafepay into your Magento 2 webshop.

The MultiSafepay extension for Magento 2 has support for:

Paymentmethods:
1. iDEAL
2. Klarna
3. Dotpay
4. EPS
5. ING Home'Pay
6. Belfius
7. AliPay
8. KBC
9. TrustPay
10. PayPal
11. Pay After Delivery
12. Direct Debit
13. American Express
14. Bancontact
15. Maestro
16. SOFORT Banking
17. Bank transfer
18. Giropay
19. Mastercard
20. Visa
21. E-Invoicing
22. Ferbuy
23. Paysafecard
24. Trustly
25. AfterPay
26. Betaalplan

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
19. VVV Cadeaukaart
20. Podium
21. Winkel Cheque

Next to the above it has support for
1. Multistore setup
2. Magestore one-step-checkout
3. Mageplaza one-step-checkout
4. Refunds from within Magento 
5. Auto update transaction to shipped for Pay After Delivery, Klarna and E-invoicing
6. Cart is active on canceled transaction
7. iDEAL issuer selection within Magento
8. Credit card gateway, this one is optional and can be used to offer a grouped Credit card payment method with a card dropdown.
 
#### Installation of payment link in order confirmation mail for backend orders
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