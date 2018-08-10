# Release Notes - Magento 2 MultiSafepay plug-in 1.5.1 (Aug 24, 2018) #

## Added ##
+ PLGMAGTWOS-189: Add order status of MSP transaction
+ PLGMAGTWOS-224: Add refund support for FPT in Klarna/PAD.
+ PLGMAGTWOS-243: Add reason of declined payment to cancel page
+ PLGMAGTWOS-260: Add default translations

## Changed ##
+ PLGMAGTWOS-144: Check usage of the object manager and replace by usage of DI
+ PLGMAGTWOS-262: Expand order description
+ PLGMAGTWOS-274: Direct transaction for E-Invoicing
+ PLGMAGTWOS-273: Update payment method logos for different languages
+ PLGMAGTWOS-286: Resolve Magento2 CodeSniffer results

## Fixed ##
+ PLGMAGTWOS-290: Remove undefined order label from order statuses
+ PLGMAGTWOS-240: Round quantity when possible
+ PLGMAGTWOS-249: Orders not being updated when giftcard order contains different api key.
+ PLGMAGTWOS-250: Updating payment method doesn't show giftcard code within order notes
+ PLGMAGTWOS-278: Prevent error in log when notification is called for non existing order
+ PLGMAGTWOS-232: Trim whitespace from specific consumer fields
+ PLGMAGTWOS-230: Correct line endings

# Release Notes - Magento 2 MultiSafepay plug-in 1.5.0 (May 25th, 2018) #

## Changes ##
+ PLGMAGTWOS-256: Add support for Santander Betaalplan
+ PLGMAGTWOS-251: Add support for iDEAL QR payment method
+ PLGMAGTWOS-258: Add support for Afterpay payment method
+ PLGMAGTWOS-257: Add support for Trustly payment method
+ PLGMAGTWOS-235: Improve keepCartAlive check
+ PLGMAGTWOS-254: Improve ip validation for Magento2 versions before 2.1.8 when multiple ip addresses are returned
+ PLGMAGTWOS-264: Remove double defined payment_action in config.xml
+ PLGMAGTWOS-255: Improve state field to use 2 letters abbreviation
+ PLGMAGTWOS-269: Shipping/Countries hide when disabled in backend
+ PLGMAGTWOS-266: Corrected ratio of Santander Betaalplan logo
+ PLGMAGTWOS-186: Improve FastCheckout logo localization
+ PLGMAGTWOS-252: Updated Klarna logo

# Release Notes - Magento 2 MultiSafepay plug-in 1.4.9 (Feb 21th, 2018) #

## Changes ##
+ PLGMAGTWOS-237, Prevent call to getMethodInstance in RestoreQuote when API returns an error.
+ PLGMAGTWOS-229, Add SOAP event handler for shipping.
+ PLGMAGTWOS-236, Add support for new payment method Betaalplan (Santander).
+ PLGMAGTWOS-233, Correct ING Homepay gateway code.

# Release Notes - Magento 2 MultiSafepay plug-in 1.4.8 (Jan 4th, 2018) # 

## Changes ## 
+ PLGMAGTWOS-223, Fixes Id required error when creating an order from the backend i.c.w. a MultiSafepay payment method.
+ PLGMAGTWOS-219. Keep cart alive function is now configurable. Merchants can now choose to not restore the cart, avoiding any stock issues because magento processes stock not correctly (https://github.com/magento/magento2/issues/9969)
+ PLGMAGTWOS-220. FPT are now processed within the transaction request.
+ PLGMAGTWOS-205. Added an option to reset the payment method for when a payment link was created when an order was added through the Magento backend.
+ PLGMAGTWOS-221. Custom "pending" state can now be used.

# Release Notes - Magento 2 MultiSafepay plug-in 1.4.7 (Nov 30th, 2017) # 

## Changes ## 
+ PLGMAGTWOS-211 - Added Support for Fooman Payment Surcharge
+ PLGMAGTWOS-216 - Improve error handling when client error occurs
+ PLGMAGTWOS-216 - Fix blank page for connectivity issues
+ PLGMAGTWOS-79 - Include quantity in discount calculation to prevent 1027
+ PLGMAGTWOS-79 - Include discount into item price
+ PLGMAGTWOS-204 - Removed js on success page to prevent customer-data error
+ PLGMAGTWOS-203 - Refactored and fixed restorequote on backbutton
+ PLGMAGTWOS-212 - FCO division by zero when getting the quantity of quote_item
+ PLGMAGTWOS-208 - Add order notes for banktransfer
+ PLGMAGTWOS-207 - create_paylink is now respecting store view
+ PLGMAGTWOS-202 - Changed rounding to 10 in calculations
+ PLGMAGTWOS-201 - only use shipping address data if the order can be shipped and the addres is provided.
+ PLGMAGTWOS-203 - No cart restore on banktransfer by MultiSafepay
+ PLGMAGTWOS-196 - Added a comment for the cancelled status in combination with Second Chance.
+ PLGMAGTWOS-198 - Improved code on Cancel url for unlocking of the lockfile.

# Release Notes - Magento 2 MultiSafepay plug-in 1.4.6 (OCT 9th, 2017) # 

## Changes ## 
+ PLGMAGTWOS-23 - FCO incorrect use of payment_details_type
+ PLGMAGTWOS-148 - Improve payment images localization
+ PLGMAGTWOS-158 - Fastcheckout completed doesn't clear shopping_cart
+ PLGMAGTWOS-167 - Check item quantity used for example 12 or 12.0000 (both processed as 12)
+ PLGMAGTWOS-170 - FCO transaction creates new addresses all the time instead of checking if it already exists.
+ PLGMAGTWOS-171 - Have defaults for order statuses
+ PLGMAGTWOS-176 - FCO correct spelling mistake shpping_taxed
+ PLGMAGTWOS-177 - Refactor status code toOptionArray
+ PLGMAGTWOS-178 - getTierPrice doesn't return base_price, leads to undefined property error in some cases
+ PLGMAGTWOS-179 - product_price array defined but not used
+ PLGMAGTWOS-181 - Add support for disabling creation of paymentlinks
+ PLGMAGTWOS-182 - Localized Exception after upgrade MSP 1.4.5
+ PLGMAGTWOS-184 - Fatal error: Uncaught Error: Call to a member function getAdditionalInformation() on boolean
+ PLGMAGTWOS-185 - Add yourgift logo
+ PLGMAGTWOS-187 - Correct/remove payment images from localized folder
+ PLGMAGTWOS-188 - Add creditcard logo
+ PLGMAGTWOS-190 - Add shipping data to transaction request
+ PLGMAGTWOS-191 - Add check if transaction exists before shipment
+ PLGMAGTWOS-192 - etc\config.xml contains wrong default value for transaction_currency
+ PLGMAGTWOS-195 - Undefined property payment_details
+ PLGMAGTWOS-200 - Call restorequote just for MSP payment methods

# Release Notes - Magento 2 MultiSafepay plug-in 1.4.5 (August 25th, 2017) # 

## Changes ## 
+ Fixes PLGMAGTWOS-94. Online Refund integration support has been added for full and partial refunds
+ Fixes PLGMAGTWOS-110. Added comments to enable all Magento statuses available to link with MultiSafepay transaction statuses.
+ Fixes PLGMAGTWOS-137. We now store if a processed transaction was a FCO transaction. Based on this we process the refunds differently so we always use the correct ID to start the refund.
+ Fixes PLGMAGTWOS-152. We now set the currency for the quote when creating the quote->order so that it matches the quote currency when using a different currency within the storeview.
+ Fixed textual errors for #PLGMAGTWOS-155
+ Fixes #PLGMAGTWOS-154. Corrected and improved status checks
+ Fixes PLGMAGTWOS-154 changed MSP statuses to MSP constants instead of using Magento values.
+ Fixes #PLGMAGTWOS-162. Added check if invoice is loaded from within the admin.
+ Fixes #PLGMAGTWOS-156. Moved fastcheckout transaction check to the helper.
+ Fixed issue with chargeback status #PLGMAGTWOS-154
+ Fixes #PLGMAGTWOS-166. Now uses fco transaction detection to determine what ID to use for shipment function
+ Fixes #PLGMAGTWOS-165. Shipment function didn't include the tracking number.
+ Fixes #PLGMAGTWOS-159. Partially fixed for shipment function.
+ Fixes #PLGMAGTWOS-161. Removed unused type in refund data
+ Fixes #PLGMAGTWOS-168. Update composer with PHP 7.1
+ Fixes #PLGMAGTWOS-169. Fastcheckout buttons only visible for quote currency EUR.
+ Fixes #PLGMAGTWOS-172 improved undo cancel function so it does not overwrite the $status variable.
+ Fix PLGMAGTWOS-113 overwrite payment method with actual method. Affects banktransfer/2nd chance
+ Added license/disclaimer
+ Updated default file permissions following PLGMAGTWOS-149
+ Fixes PLGMAGTWOS-175, updated version number for FCO transactions

# Release Notes - Magento 2 MultiSafepay plug-in 1.4.4 (August 4th, 2017) # 

## Changes ## 
+ Fixes #24. Added notification to cart page after redirect back to the store once the transaction was canceled/declined
+ Fixes PLGMAG-304. Only allow Klarna when billing and shipping address are the same (Klarna regulation)
+ Fixes #25. Improvements to the process locking.
+ Fixed wrong urls used when order is created from the Magento backend.
+ Fixes #26 Usage of protected attribute instead of getter method.
+ Fixes clearing var folder
+ Refactor setApiKey/setApiUrl
+ Fix incorrect api_key with backend orders and multistore environment
+ Removed checking mode for unlockProcess
+ Fix PLGMAGTWO-137. Unset days_active when seconds_active is set.
+ Fix PLGMAGTWO-134. Observe ship event when REST API is used to ship order.
+ Configurable option to used the base currency, or store currency
+ Fixed PLGMAGTWO-138. Added support for MultiSafepay Magento 2 Payment Fee extension. (Will become available soon)
+ Fixes PLGMAGTWOS-146. Fixed an issue causing invoices not being created when the storeview currency was used for transactions. This issue was caused by an issue with Magento, described in https://github.com/magento/magento2/commit/c0c24116c3a790db671ae1831c09a4e51adf0549 In order to get this fixed its important to manually apply the fix described on that page when using a version lower than Magento 2.1.8.
+ Online refunding when using a storeview currency causes issues because Magento provides the base amount. When converting we have rounding issues causing a mismatch when refunding. After investigation we found an overall issue with refunding and for now have disabled refunding from within Magento. A new refunding implementation will be added to the next release.(1.4.5)

# Release Notes - Magento 2 MultiSafepay plug-in 1.4.3 (May 24th, 2017) # 

## Bugs ##
+ Fixed compilation errors
+ Fixed the shippping title used
+ Fixed an issue with the stock items

# Release Notes - Magento 2 MultiSafepay plug-in 1.4.2 (May 23th, 2017) # 

## Bugs ##
+ Fixed an issue causing the Fastcheckout button to be visible even when disabled
+ Removed the double extra_phone field from system.xml
+ Improved check for MultiSafepay payment object

# Release Notes - Magento 2 MultiSafepay plug-in 1.4.1 (May 17th, 2017) # 

## Bugs ##
+ Fixed issues with compilation

# Release Notes - Magento 2 MultiSafepay plug-in 1.4.0 (May 17th, 2017) # 

## Bugs ##
+ Fixed a bug causing the ideal issuer not being processed. This resulted in a double ideal issuer selection.
+ Fixed a typo for parfumcadeaukaart in the layout files.
+ Updated check for Klarna invoice comment so it’s not added for other payment methods.
+ Fixed an invalid tax rate with Pay After Delivery when shipping percentage is 0
+ Fixed an issue with wrong gateway codes used for manual created orders.
+ Order confirmation was submitted on status Expired while the configuration was set to submit it only when the transaction was paid.
+ Added a check for specificcountry, fixing an undefined notice

## Improvements ##
+ We added a better IP detection for transaction requests
+ Logging is now done within the Client and logs requests/responses to var/log/multisafepay.log.
+ Added process locking to solve double invoice and orders when processes start simultaneously
+ Added a check for the totalpaid amount. On some installations this is not updated, causing the order not to go to complete after shipment.
+ Removed the banktransfer url from the order comment for manual created orders as there is no need to communicate the success url as normally it would be an url to the payment pages
+ The cart is now correctly active on cancel or when using the back button
+ When the transaction status is uncleared, a comment is added to the order about this. The order will not be set to payment_review and will go to completed once the transaction has been approved. (This now works like in the Magento 1 extension)
+ Added version to menu item

## Features ##
+ Added support for Seconds active. Seconds active will set how long a payment link can be used in seconds.
+ When an order is shipped, this will be updated within the transaction at MultiSafepay
+ Added a debug option so that it can be enabled/disabled, only curl errors are logged by default.
+ Images are now loaded based on language. Images can be updates based on the language.
+ Added an option where you can configure the pre-selected payment method for the frontend.
+ Added support for Fastcheckout
+ Added support for TrustPay
+ Added support for KBC
+ Added support for AliPay
+ Added support for Belfius
+ Added support for ING Homepay

# Release Notes - Magento 2 MultiSafepay plug-in 1.3.0 (Jan 25th, 2017** 

## Changes ## 
+ RequestInterface already exists in context object
+ PLGMAGTWO-82. Added givacard giftcard gateway
+ Fixed issue causing wrong state to be used for initial status update.

# Release Notes - Magento 2 MultiSafepay plug-in 1.2.0 (Dec 27th, 2016) # 

## Changes ## 
+ Improved order confirmation email logic. Email won't be submitted when an internal transaction is expired and no real transaction exists
+ Adding a rounding to the amount
+ Fixed type assignment instead of comparison
+ Set namespace for core exception
+ Fixes PLGMAGTWO-71, throw error on api error when refunding
+ Fixes PLGMAGTWO-68, removed echo's from the extension and replaced it with setContent
+ Removed direct use of superglobal $_GET to get transaction type and transactionid
+ Disabled notification processing when no timestamp was set, solving issues with double invoices when callbacks are processed to fast.

## Features ##
+ Added support for the Get Payment Update function
+ Added an option to allow payment link creation for manually created orders.
+ Added a CreditCard gateway with a dropdown for CC brands. This allows for less options during the checkout overview.

# Release Notes - Magento 2 MultiSafepay plug-in 1.1.0 (Okt 13th, 2016) # 

## Changes ## 
+ Better check on redirect data, fixing error when transaction details are not available yet.
+ Added check if API key is configured on issuer request. The issuer request is done when reaching the checkout page using the config provider. Not checking if the API key is configured will cause an exception after installation when the key is not yet configured but the static-content is already deployed.
+ Fixed bug with the ACL.xml. User roles can now select the MultiSafepay tabs to allow/disallow access for a certain user
+ Fixed bug with Language detection, causing transaction language always be set to nl_NL and resulting in Dutch language pages and transaction e-mails
+ Fixed the path of the gateway images that was hardcoded to "pub", this is now dynamically set by the config provider
+ Fixed a bug causing uncleared transactions not being updated after the transaction status has been changed to completed

## Features ##
+ Added support for EPS
+ Added support for Ferbuy
+ We added an option to configure when the order confirmation email needs to be submitted. You can select "after order placing", "After a processed transaction" and "after a paid transaction"
+ Added official support for the "one-step-checkout" from MagePlaza
+ Added configuration options for more transactions statuses. You can now configure the status for "Expired", "Cancelled", "Chargeback", "Declined" and "New Order"
+ We added a multisafepay.log logger that will store all API requests
+ Added support for E-invoicing

# Release Notes - Magento 2 MultiSafepay plug-in 1.0.6 (Jul 21th, 2016) #

## Changes ## 
+ Extended the gateway configuration file so that all restrictions valus are set, causing them to be configured one saving of the configuration. This would reduce the support questions because of merchants that forget to configure the restrictions. Default all is now configured.
+ This fix sets the payment method to disabled by default so it only has to be enabled to show it on the frontend. This change was needed because of PLGMAGTWO-25. PLGMAGTWO-25 now sets the restrictions by default, causing all payment method to show on config save. PLGMAGTWO-24 prevents everything to show as it must be enabled first.
+ Check was nog complete as we checked if the housenumber was set, not if it had an empty value, causing the check to fail and no housenumber to be added to the transaction request. This caused a rejection for Pay after Delivery and Klarna.
+ Added gateway images in the same format as the default PayPal plugin. Restructured the folder so in is setup in a way it can be copied to the root of the Magento installation as it was not clear for merchants that the code folder needed to be created when it was not existing yet. Added all images.
+ Changed the banktransfer payment method to a "direct" payment method. This will remove the MultiSafepay payment page and submit the payment details by e-mail to the consumer.

# Release Notes - Magento 2 MultiSafepay plug-in 1.0.5 (Jne 29th, 2016) # 

## Important ##
+ When using Magento 2.1 this release or higher should be used. Older plugin releases are not compatible with Magento 2.1

## Changes ##
+ Added more space tween the iDEAL issuer selection and the address data.
+ Magento 2.1 update changed the way you could get the version number of Magento, this causing our extension to fail. This has been updated to the new way to get the Magento version information.
+ Updated the version within the module.xml so it matches the real Multisafepay used version number
+ Configuration file was not yet correct for Multistore support, causing configuration options not to show within the backend. Due to this issue payment method didn't show within the extra store.
+ Added a check for the quote. Seems like Magento update 2.1. introduced a bug causing Magento not to call the isavailable function with the quote param.

# Release Notes - Magento 2 MultiSafepay plug-in 1.0.4 (Jne 23th, 2016** 

## Changes ## 
+ Fixes bug causing empty options within the iDEAL issuer selection

# Release Notes - Magento 2 MultiSafepay plug-in 1.0.3 (Jne 22th, 2016) # 

## Changes ## 
+ Ideal issuer selection can now be done during checkout
+ Refunding from within Magento using PAD or Klarna is not possible. This will be integrated when Partial Refunds are also possible.
+ Discounts/coupons did not yet get processed for PAD/Klarna. This is now solved. Important: Tax settings should be set to: Apply Customer Tax= Before Discount, Apply Discount On Prices= Including tax. Using other settings results in a 1027 error because Tax can't be calculated based on the other settings.
+ Error notice when Pay After Delivery was used with a zero shipping cost. This resulted in a division by zero within the calculation. 
+ Added option to disable shippingmethod restriction. This can be used when third party shipping extensions are used that are not loaded to the list by Magento
+ Check for empty shipping method. If shipping method is emtpy then do show the payment methods as it can also be downloadable goods
+ Added Dotpay gateway
+ Renamed paypal xml to paypalmsp to avoid conflict with existing paypal extensions.

# Release Notes - Magento 2 MultiSafepay plug-in 1.0.2 (May 26th, 2016) # 

## Fixes ##
+ Fixed an undefined notice and some issues with Klarna en PAD. Shipping percentage was calculated wrong resulted in wrong amounts (1027 error for PAD)/li> 
+ Fixes undefined index bug in magento caused by Magento not checking for missing data
+ Fixed bug with banktransfer not showing
+ Added missing namespace declaration in unused gateways and issuers files in API wrapper
+ Removed beta version from composer file and update name