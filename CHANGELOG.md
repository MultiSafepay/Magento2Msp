## 1.12.1
Release date: Jun 25th, 2020

### Fixed
+ PLGMAGTWOS-628: Fix reopened orders stuck on pending
+ Always set store locale as locale - by Hexmage
+ Solve IE error Object doesn't support property or method 'includes' - by lewisvoncken
+ Fix translatable error message - by peterjaap

***

## 1.12.0
Release date: May 8th, 2020

### Added
+ PLGMAGTWOS-607: Add customizable order status for Bank Transfer
+ PLGMAGTWOS-611: Add REST endpoints to support PWA

### Fixed
+ PLGMAGTWOS-613: Fix stock issues with MSI and reopening orders

### Changed
+ Rename Santander Betaalplan to Santander Betaal per Maand

***

## 1.11.0
Release date: April 2nd, 2020

### Added
+ PLGMAGTWOS-597: Add Direct Bank Transfer

### Changed
+ PLGMAGTWOS-544: Replace creditcard gateway with improved creditcard gateway
+ Discontinue support for Magento 2.1.x

***

## 1.10.0
Release date: March 17th, 2020

### Added
+ PLGMAGTWOS-334: Add Apple Pay.

### Fixed
+ Fix JQueryUI warning.

### Removed
+ PLGMAGTWOS-581: Remove redundant address2 from the transaction request.

***

## 1.9.0
Release date: February 26th, 2020

### Added
+ PLGMAGTWOS-334: Add support for custom totals.

### Changed
+ PLGMAGTWOS-539: Prevent selection of billing address for Pay After Delivery.

### Fixed
+ Dispatch event before transaction request is sent (Thanks to [duckchip](https://github.com/duckchip)).

***

## 1.8.0
Release date: December 12th, 2019

### Added
+ PLGMAGTWOS-506: Enable fail save option for saving transactions.
+ PLGMAGTWOS-504: Add link to the MultiSafepay update and install notification message that redirects to the MultiSafepay documentation.
+ PLGMAGTWOS-483: Add Magento 2 module dependencies to composer.
+ PLGMAGTWOS-479: Add PHP dependencies to composer.
+ PLGMAGTWOS-471: Add option to hide MultiSafepay payment method from checkout.
+ PLGMAGTWOS-454: Add translation for 'Choose your creditcard' in German.

### Changed
+ PLGMAGTWOS-473: Mark MultiSafepay configuration values as environment specific.
+ PLGMAGTWOS-481: Change configuration allowed groups when selecting '-- Please Select --' to show payment method for all groups.
+ PLGMAGTWOS-468: Decline orders if refund contains adjustments.

### Fixed
+ PLGMAGTWOS-505: Prevent refunds at MultiSafepay with zero amount.
+ PLGMAGTWOS-284: Fix error when redirecting to payment page if cart contains configurable and simple product in this order.
+ PLGMAGTWOS-498: Fix uncaught TypeErrors when FastCheckout is enabled.
+ PLGMAGTWOS-449: Fix payment redirect to cart instead of success page if the payment is done with the iDEAL app and iOS.

### Removed
+ PLGMAGTWOS-514: Remove FerBuy payment method.
+ PLGMAGTWOS-452: Remove Klarna reservation number from order note.

***

## 1.7.1
Release date: July 2nd, 2019

### Fixed
+ PLGMAGTWOS-459: Fixed orders not completed when having a prefix

***

## 1.7.0
Release date: June 26th, 2019

### Added
+ PLGMAGTWOS-282: Added MultiSafepay payment method.
+ PLGMAGTWOS-308: Added tokenization for the creditcard gateway.
+ PLGMAGTWOS-404: Added payment link to order confirmation email.
+ PLGMAGTWOS-350: Added order log record if MultiSafepay amount is not identical to order amount.
+ PLGMAGTWOS-375: Added validation on cancel URL (Thanks to [Luciferiix](https://github.com/Luciferiix)).
+ PLGMAGTWOS-376: Added validation input on redirect and notification URL (Thanks to [Luciferiix](https://github.com/Luciferiix)).
+ PLGMAGTWOS-365: Added globally observe shipment and order placement.
+ PLGMAGTWOS-288: Added payment specification when paid with coupon and payment method.

### Changed
+ PLGMAGTWOS-390: Changed parsing of address fields into street and apartment.
+ PLGMAGTWOS-315: Corrected spelling for ING Home'Pay.
+ PLGMAGTWOS-298: Corrected spelling for default selected method.
+ PLGMAGTWOS-371: Extract setMultisafepayStatus so it can be used by third party plugins (Thanks to [Tjitse-E](https://github.com/Tjitse-E)).
+ PLGMAGTWOS-302: Changed VVV Bon to VVV Cadeaukaart.

### Fixed
+ PLGMAGTWOS-381: Fixed incorrect amount for shipping cost in combination with tax and discount.
+ PLGMAGTWOS-370: Fixed getMethodInstance error after checkout.
+ PLGMAGTWOS-339: Fixed Api key not found for Fastcheckout on the live environment.
+ PLGMAGTWOS-346: Fixed module.xml missing sequence breaking filters in sales_order view (Thanks to [HeadlineInteractive](https://github.com/HeadlineInteractive)).
+ PLGMAGTWOS-277: Prevent protected property error in rare cases.
+ PLGMAGTWOS-331: Prevent htmlParseEntityRef in exception.log when viewing orders (Thanks to [teun-loonen](https://github.com/teun-loonen)).

### Removed
+ PLGMAGTWOS-287: Remove Klarna invoice link.
+ PLGMAGTWOS-383: Remove support for days and seconds active for bank transfer.
+ PLGMAGTWOS-301: Remove manually created payment links for Klarna, Pay After Delivery, AfterPay and Betaalplan
+ PLGMAGTWOS-418: Remove second chance mail on backend orders.

***

## 1.6.3
Release date: November 29th, 2018  

### Changes
+ PLGMAGTWOS-222: Remove PHP requirement from composer.json to fix incompatible issue with Magento 2.3.0 and PHP 7.2 requirement

***

## 1.6.2
Release date: October 23th, 2018  

### Changes
+ PLGMAGTWOS-328: Update Dutch translations. Thanks to [Jeroen Alewijns](https://nl.linkedin.com/in/jeroenalewijns).

### Fixes
+ PLGMAGTWOS-324: Fix expected identifier error in checkout with Internet Explorer 11. Thanks to [lexbeelen](https://github.com/lexbeelen)
+ PLGMAGTWOS-322: Fix reopened paid orders which didn't get an invoice
+ PLGMAGTWOS-231: Enable refund shipping amount when shipping includes tax

***

## 1.6.1
Release date: October 16th, 2018

### Changes
+ PLGMAGTWOS-323: Fix Magento 2.2.6 issue where paid orders were stuck to Pending. Thanks to [Tjitse-E](https://github.com/Tjitse-E) for the patch.

***

## 1.6.0
Release date: September 21th, 2018

### Features
+ PLGMAGTWOS-319: [Tokenization] Deactive text
+ PLGMAGTWOS-317: [Tokenization] Order_id data type changed from INTEGER to TEXT
+ PLGMAGTWOS-316: [Tokenization] Read customerID from order instead of session
+ PLGMAGTWOS-310: Bump version to 1.6.0
+ PLGMAGTWOS-309: Update CHANGELOG.md
+ PLGMAGTWOS-295: Add Tokenization
+ PLGMAGTWOS-306: Refactor UpgradeData according to Magento 2 standard
+ PLGMAGTWOS-305: Take table prefix into account when adding _multisafepay_status_
+ PLGMAGTWOS-303: Fix compilation error on Magento 2.1 with context object

***

## 1.5.0
Release date: May 25th, 2018
### Changes
+ PLGMAGTWOS-256: Add support for Santander Betaalplan
+ PLGMAGTWOS-251: Add support for iDEAL QR payment method
+ PLGMAGTWOS-258: Add support for Afterpay payment method
+ PLGMAGTWOS-257: Add support for Trustly payment method
+ PLGMAGTWOS-235: Improve keepCartAlive check
+ PLGMAGTWOS-254: Improve ip validation for Magento 2 versions before 2.1.8 when multiple ip addresses are returned
+ PLGMAGTWOS-264: Remove double defined payment_action in config.xml
+ PLGMAGTWOS-255: Improve state field to use 2 letters abbreviation
+ PLGMAGTWOS-269: Shipping/Countries hide when disabled in backend
+ PLGMAGTWOS-266: Corrected ratio of Santander Betaalplan logo
+ PLGMAGTWOS-186: Improve FastCheckout logo localization
+ PLGMAGTWOS-252: Updated Klarna logo

---

## 1.4.9
Release date: Feb 21th, 2018
### Changes
+ PLGMAGTWOS-237, Prevent call to getMethodInstance in RestoreQuote when API returns an error.
+ PLGMAGTWOS-229, Add SOAP event handler for shipping.
+ PLGMAGTWOS-236, Add support for new payment method Betaalplan (Santander).
+ PLGMAGTWOS-233, Correct ING Home'Pay gateway code.

***

## 1.4.8
Release date: Jan 4th, 2018
### Changes
+ PLGMAGTWOS-223, Fixes Id required error when creating an order from the backend i.c.w. a MultiSafepay payment method.
+ PLGMAGTWOS-219. Keep cart alive function is now configurable. Merchants can now choose to not restore the cart, avoiding any stock issues because Magento processes stock not correctly (https://github.com/magento/magento2/issues/9969)
+ PLGMAGTWOS-220. FPT are now processed within the transaction request.
+ PLGMAGTWOS-205. Added an option to reset the payment method for when a payment link was created when an order was added through the Magento backend.
+ PLGMAGTWOS-221. Custom "pending" state can now be used.

***

## 1.4.7
Release date: Nov 30th, 2017
### Changes
+ PLGMAGTWOS-211 - Added Support for Fooman Payment Surcharge
+ PLGMAGTWOS-216 - Improve error handling when client error occurs
+ PLGMAGTWOS-216 - Fix blank page for connectivity issues
+ PLGMAGTWOS-79 - Include quantity in discount calculation to prevent 1027
+ PLGMAGTWOS-79 - Include discount into page price
+ PLGMAGTWOS-204 - Removed js on success page to prevent customer-data error
+ PLGMAGTWOS-203 - Refactored and fixed restorequote on backbutton
+ PLGMAGTWOS-212 - FCO division by zero when getting the quantity of quote_item
+ PLGMAGTWOS-208 - Add order notes for bank transfer
+ PLGMAGTWOS-207 - create_paylink is now respecting store view
+ PLGMAGTWOS-202 - Changed rounding to 10 in calculations
+ PLGMAGTWOS-201 - Only use shipping address data if the order can be shipped and the address is provided.
+ PLGMAGTWOS-203 - No cart restore on bank transfer by MultiSafepay
+ PLGMAGTWOS-196 - Added a comment for the cancelled status in combination with Second Chance.
+ PLGMAGTWOS-198 - Improved code on Cancel URL for unlocking of the lockfile.

***

## 1.4.6
Release date: oct 9th, 2017
### Changes
+ PLGMAGTWOS-23 - FCO incorrect use of payment_details_type
+ PLGMAGTWOS-148 - Improve payment images localization
+ PLGMAGTWOS-158 - Fastcheckout completed doesn't clear shopping_cart
+ PLGMAGTWOS-167 - Check pages quantity used for example 12 or 12.0000 (both processed as 12)
+ PLGMAGTWOS-170 - FCO transaction creates new addresses all the time instead of checking if it already exists.
+ PLGMAGTWOS-171 - Have defaults for order statuses
+ PLGMAGTWOS-176 - FCO correct spelling mistake shipping_taxed
+ PLGMAGTWOS-177 - Refactor status code toOptionArray
+ PLGMAGTWOS-178 - getTierPrice doesn't return base_price, leads to undefined property error in some cases
+ PLGMAGTWOS-179 - product_price array defined but not used
+ PLGMAGTWOS-181 - Add support for disabling creation of payment links
+ PLGMAGTWOS-182 - Localized Exception after upgrade MultiSafepay 1.4.5
+ PLGMAGTWOS-184 - Fatal error: Uncaught Error: Call to a member function getAdditionalInformation() on boolean
+ PLGMAGTWOS-185 - Add yourgift logo
+ PLGMAGTWOS-187 - Correct/remove payment images from localized folder
+ PLGMAGTWOS-188 - Add credit card logo
+ PLGMAGTWOS-190 - Add shipping data to transaction request
+ PLGMAGTWOS-191 - Add check if transaction exists before shipment
+ PLGMAGTWOS-192 - etc\config.xml contains wrong default value for transaction_currency
+ PLGMAGTWOS-195 - Undefined property payment_details
+ PLGMAGTWOS-200 - Call restorequote just for MultiSafepay payment methods

***

## 1.4.5
Release date: August 25th, 2017
### Changes
+ Fixes PLGMAGTWOS-94. Online Refund integration support has been added for full and partial refunds
+ Fixes PLGMAGTWOS-110. Added comments to enable all Magento statuses available to link with MultiSafepay transaction statuses.
+ Fixes PLGMAGTWOS-137. We now store if a processed transaction was an FCO transaction. Based on this we process the refunds differently, so we always use the correct ID to start the refund.
+ Fixes PLGMAGTWOS-152. We now set the currency for the quote when creating the quote->order so that it matches the quote currency when using a different currency within the storeview.
+ Fixed textual errors for #PLGMAGTWOS-155
+ Fixes #PLGMAGTWOS-154. Corrected and improved status checks
+ Fixes PLGMAGTWOS-154 changed MultiSafepay statuses to MultiSafepay constants instead of using Magento values.
+ Fixes #PLGMAGTWOS-162. Added check if invoice is loaded from within the backend.
+ Fixes #PLGMAGTWOS-156. Moved fastcheckout transaction check to the helper.
+ Fixed issue with chargeback status #PLGMAGTWOS-154
+ Fixes #PLGMAGTWOS-166. Now uses fco transaction detection to determine what ID to use for shipment function
+ Fixes #PLGMAGTWOS-165. Shipment function didn't include the tracking number.
+ Fixes #PLGMAGTWOS-159. Partially fixed for shipment function.
+ Fixes #PLGMAGTWOS-161. Removed unused type in refund data
+ Fixes #PLGMAGTWOS-168. Update composer with PHP 7.1
+ Fixes #PLGMAGTWOS-169. Fastcheckout buttons only visible for quote currency EUR.
+ Fixes #PLGMAGTWOS-172 improved undo cancel function, so it does not overwrite the $status variable.
+ Fix PLGMAGTWOS-113 overwrite payment method with actual method. Affects bank transfer/Second chance
+ Added license/disclaimer
+ Updated default file permissions following PLGMAGTWOS-149
+ Fixes PLGMAGTWOS-175, updated version number for FCO transactions

***

## 1.4.4
Release date: August 4th, 2017
### Changes
+ Fixes #24. Added notification to cart page after redirect back to the store once the transaction was cancelled/declined
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
+ Configurable option to use the base currency, or store currency
+ Fixed PLGMAGTWO-138. Added support for MultiSafepay Magento 2 Payment Fee plugin. (Will become available soon)
+ Fixes PLGMAGTWOS-146. Fixed an issue causing invoices not being created when the storeview currency was used for transactions. This issue was caused by an issue with Magento, described in https://github.com/magento/magento2/commit/c0c24116c3a790db671ae1831c09a4e51adf0549 In order to get this fixed it�s important to manually apply the fix described on that page when using a version lower than Magento 2.1.8.
+ Online refunding when using a storeview currency causes issues because Magento provides the base amount. When converting we have rounding issues causing a mismatch when refunding. After investigation we found an overall issue with refunding and for now have disabled refunding from within Magento. A new refunding implementation will be added to the next release.(1.4.5)

***

## 1.4.3
Release date: May 24th, 2017
### Bugs
+ Fixed compilation errors
+ Fixed the shippping title used
+ Fixed an issue with the stock pages

***

## 1.4.2
Release date: May 23th, 2017
### Bugs
+ Fixed an issue causing the Fastcheckout button to be visible even when disabled
+ Removed the double extra_phone field from system.xml
+ Improved check for MultiSafepay payment object

***

## 1.4.1
Release date: May 17th, 2017
### Bugs
+ Fixed issues with compilation

***

## 1.4.0
Release date: May 17th, 2017
### Bugs
+ Fixed a bug causing the iDEAL issuer not being processed. This resulted in a double iDEAL issuer selection.
+ Fixed a typo for parfumcadeaukaart in the layout files.
+ Updated check for Klarna invoice comment so it’s not added for other payment methods.
+ Fixed an invalid tax rate with Pay After Delivery when shipping percentage is 0
+ Fixed an issue with wrong gateway codes used for manual created orders.
+ Order confirmation was submitted on status Expired while the configuration was set to submit it only when the transaction was paid.
+ Added a check for specific country, fixing an undefined notice

### Improvements
+ We added a better IP detection for transaction requests
+ Logging is now done within the Client and logs requests/responses to var/log/multisafepay.log.
+ Added process locking to solve double invoice and orders when processes start simultaneously
+ Added a check for the total paid amount. On some installations this is not updated, causing the order not to go to complete after shipment.
+ Removed the bank transfer URL from the order comment for manual created orders as there is no need to communicate the success URL as normally it would be an URL to the Payment pages
+ The cart is now correctly active on cancel or when using the back button
+ When the transaction status is uncleared, a comment is added to the order about this. The order will not be set to payment review and will go to completed once the transaction has been approved. This now works like in the Magento 1 plugin.
+ Added version to menu item

### Features
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
+ Added support for ING Home'Pay

***

## 1.3.0
Release date: Jan 25th, 2017**
### Changes
+ RequestInterface already exists in context object
+ PLGMAGTWO-82. Added givacard gift card gateway
+ Fixed issue causing wrong state to be used for initial status update.

***

## 1.2.0
Release date: Dec 27th, 2016
### Changes
+ Improved order confirmation email logic. email won't be submitted when an internal transaction is expired, and no real transaction exists
+ Adding a rounding to the amount
+ Fixed type assignment instead of comparison
+ Set namespace for core exception
+ Fixes PLGMAGTWO-71, throw error on api error when refunding
+ Fixes PLGMAGTWO-68, removed echoes from the plugin and replaced it with setContent
+ Removed direct use of superglobal GET to get transaction type and transactionid
+ Disabled notification processing when no timestamp was set, solving issues with double invoices when callbacks are processed to fast.

### Features
+ Added support for the Get Payment Update function
+ Added an option to allow payment link creation for manually created orders.
+ Added a CreditCard gateway with a dropdown for CC brands. This allows for less options during the checkout overview.

***

## 1.1.0
Release date: Oct 13th, 2016
### Changes
+ Better check on redirect data, fixing error when transaction details are not available yet.
+ Added check if API key is configured on issuer request. The issuer request is done when reaching the checkout page using the config provider. Not checking if the API key is configured will cause an exception after installation when the key is not yet configured but the static-content is already deployed.
+ Fixed bug with the ACL.xml. User roles can now select the MultiSafepay tabs to allow/disallow access for a certain user
+ Fixed bug with Language detection, causing transaction language to always be set to nl_NL and resulting in Dutch language pages and transaction emails
+ Fixed the path of the gateway images that was hardcoded to "pub", this is now dynamically set by the config provider
+ Fixed a bug causing uncleared transactions not being updated after the transaction status has been changed to completed

### Features
+ Added support for EPS
+ Added support for Ferbuy
+ We added an option to configure when the order confirmation email needs to be submitted. You can select "after order placing", "After a processed transaction" and "after a paid transaction"
+ Added official support for the "one-step-checkout" from MagePlaza
+ Added configuration options for more transaction statuses. You can now configure the status for "Expired", "Cancelled", "Chargeback", "Declined" and "New Order"
+ We added a multisafepay.log logger that will store all API requests
+ Added support for E-invoicing

***

## 1.0.6
Release date: Jul 21th, 2016
### Changes
+ Extended the gateway configuration file so that all restrictions values are set, causing them to be configured one saving of the configuration. This would reduce the support questions because of merchants that forget to configure the restrictions. Default all is now configured.
+ This fix sets the payment method to disabled by default, so it only has to be enabled to show it on the frontend. This change was needed because of PLGMAGTWO-25. PLGMAGTWO-25 now sets the restrictions by default, causing all payment methods to show on config save. PLGMAGTWO-24 prevents everything to show as it must be enabled first.
+ Check was not complete as we checked if the house number was set, not if it had an empty value, causing the check to fail and no house number to be added to the transaction request. This caused a rejection for Klarna.
+ Added gateway images in the same format as the default PayPal plugin. Restructured the folder so in is setup in a way it can be copied to the root of the Magento installation as it was not clear for merchants that the code folder needed to be created when it was not existing yet. Added all images.
+ Changed the bank transfer payment method to a "direct" payment method. This will remove the MultiSafepay Payment page and submit the payment details by email to the customer.

***

## 1.0.5
Release date: Jne 29th, 2016
### Important
+ When using Magento 2.1 this release or higher should be used. Older plugin releases are not compatible with Magento 2.1

### Changes
+ Added more space between the iDEAL issuer selection and the address data.
+ Magento 2.1 update changed the way you could get the version number of Magento, this causing our plugin to fail. This has been updated to the new way to get the Magento version information.
+ Updated the version within the module.xml so it matches the real MultiSafepay used version number
+ Configuration file was not yet correct for Multistore support, causing configuration options not to show within the backend. Due to this issue payment method didn't show within the extra store.
+ Added a check for the quote. Seems like Magento update 2.1. introduced a bug causing Magento not to call the _isavailable_ function with the quote param.

## 1.0.4
Release date: June 23th, 2016**
### Changes
+ Fixes bug causing empty options within the iDEAL issuer selection

***

## 1.0.3
Release date: Jne 22th, 2016
### Changes
+ iDEAL issuer selection can now be done during checkout
+ Refunding from within Magento using Pay After Delivery or Klarna is not possible. This will be integrated when Partial Refunds are also possible.
+ Discounts/coupons did not yet get processed for Pay After Delivery/Klarna. This is now solved. Important: Tax settings should be set to: Apply Customer Tax= Before Discount, Apply Discount On Prices= Including tax. Using other settings results in a 1027 error because Tax can't be calculated based on the other settings.
+ Error notice when Pay After Delivery was used with a zero-shipping cost. This resulted in a division by zero within the calculation.
+ Added option to disable shipping method restriction. This can be used when third party shipping plugins are used that are not loaded to the list by Magento
+ Check for empty shipping method. If shipping method is empty, then do show the payment methods as it can also be downloadable goods
+ Added Dotpay gateway
+ Renamed PayPal xml to paypalmsp to avoid conflict with existing PayPal plugins.

***

## 1.0.2
Release date: May 26th, 2016
### Fixed
+ Fixed an undefined notice and some issues with Klarna en Pay After Delivery. Shipping percentage was calculated wrong resulted in wrong amounts (1027 error for Pay After Delivery)/li>
+ Fixes undefined index bug in Magento caused by Magento not checking for missing data
+ Fixed bug with bank transfer not showing
+ Added missing namespace declaration in unused gateways and issuers files in API wrapper
+ Removed beta version from composer file and update name
