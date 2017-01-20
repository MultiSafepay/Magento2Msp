<?php

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

namespace MultiSafepay\Connect\Model;

use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Model\Order\Payment;
use MultiSafepay\Connect\Model\Api\MspClient;
use MultiSafepay\Connect\Helper\Data;
use Magento\Framework\AppInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;

class Connect extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_isInitializeNeeded = true;
    protected $_infoBlockType = 'Magento\Payment\Block\Info\Instructions';
    public $issuer_id = null;

    /**
     * @var string
     */
    protected $_code = 'connect';

    /**
     * @var string
     */
    //protected $_infoBlockType = 'Magento\Paypal\Block\Payment\Info';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isOffline = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canOrder = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canAuthorize = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapture = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapturePartial = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canVoid = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseInternal = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseCheckout = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canSaveCc = false;

    /**
     * @var bool
     */
    protected $_canFetchTransactionInfo = true;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canReviewPayment = false;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_requestHttp;
    protected $_client;
    protected $_mspHelper;
    protected $_gatewayCode;
    protected $_product;
    public $_invoiceSender;
    public $_stockInterface;
    public $banktransurl;
    protected $logger;
    public $_manualGateway = null;
    public $_isAdmin = false;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\RequestInterface $requestHttp
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
    \Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory, \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory, \Magento\Payment\Helper\Data $paymentData, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Payment\Model\Method\Logger $logger, \Magento\Framework\Module\ModuleListInterface $moduleList, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Checkout\Model\Session $checkoutSession, \Magento\Framework\UrlInterface $urlBuilder, \Magento\Framework\App\RequestInterface $requestHttp, \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null, \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null, array $data = []
    )
    {
        parent::__construct(
                $context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger
        );
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->_urlBuilder = $urlBuilder;
        $this->_client = new MspClient();
        $this->_requestHttp = $requestHttp;
        $this->_mspHelper = new \MultiSafepay\Connect\Helper\Data;
        $this->_minAmount = $this->getConfigData('min_order_total');
        $this->_maxAmount = $this->getConfigData('max_order_total');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_invoiceSender = $objectManager->get('\Magento\Sales\Model\Order\Email\Sender\InvoiceSender');


        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/multisafepay.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
    }

    public function transactionRequest($order, $productRepo = null)
    {
        $params = $this->_requestHttp->getParams();

        if (isset($params['issuer'])) {
            $this->issuer_id = $params['issuer'];
        }
        $billing = $order->getBillingAddress();
        $shipping = $order->getShippingAddress();
        $this->_gatewayCode = $order->getPayment()->getMethodInstance()->_gatewayCode;

        if (isset($params['creditcard'])) {
            $this->_gatewayCode = $params['creditcard'];
        }

        $environment = $this->getMainConfigData('msp_env');

        /* With Magento update 2.1 the line below no longer works */
        //$magentoInfo = new \Magento\Framework\App\ProductMetadata;
        /* above code has changed to two lines below to get it compatible with 2.1 again */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $magentoInfo = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');



        if ($environment == true) {
            $this->_client->setApiKey($this->getConfigData('test_api_key', null, $order->getPayment()->getMethodInstance()->_code));
            $this->_client->setApiUrl('https://testapi.multisafepay.com/v1/json/');
        } else {
            $this->_client->setApiKey($this->getConfigData('live_api_key', null, $order->getPayment()->getMethodInstance()->_code));
            $this->_client->setApiUrl('https://api.multisafepay.com/v1/json/');
        }


        $items = "<ul>\n";
        foreach ($order->getAllVisibleItems() as $item) {
            $items .= "<li>" . ($item->getQtyOrdered() * 1) . " x : " . $item->getName() . "</li>\n";
        }
        $items .= "</ul>\n";

        $dataCheck = $this->getConfigData('days_active', null, $order->getPayment()->getMethodInstance()->_code);
        if (isset($dataCheck)) {
            $daysActive = $dataCheck;
        } else {
            $daysActive = '30';
        }

        if ($this->_gatewayCode == 'PAYAFTER' || $this->_gatewayCode == 'KLARNA' || $this->_gatewayCode == 'EINVOICE') {
            $checkoutData = $this->getCheckoutData($order, $productRepo);
            $shoppingCart = $checkoutData["shopping_cart"];
            $checkoutData = $checkoutData["checkout_options"];
        } else {
            $shoppingCart = '';
            $checkoutData = '';
        }

        $addressData = $this->parseCustomerAddress($billing->getStreetLine(1));


        if (isset($addressData['housenumber']) && !empty($addressData['housenumber'])) {
            $street = $addressData['address'];
            $housenumber = $addressData['housenumber'];
        } else {
            $street = $billing->getStreetLine(1);
            $housenumber = $billing->getStreetLine(2);
        }

        if ($billing->getTelephone() == '-') {
            $phone = '';
        } else {
            $phone = $billing->getTelephone();
        }

        if (!empty($this->issuer_id) || $this->_gatewayCode == "BANKTRANS") {
            $type = 'direct';
        } else {
            $type = 'redirect';
        }

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Locale\Resolver $resolver */
        $resolver = $om->get('Magento\Framework\Locale\Resolver');

        if ($this->_manualGateway != null) {
            $this->_gatewayCode = $this->_manualGateway;
        }

        if ($this->_isAdmin) {
            $notification = str_replace('/admin', '', $this->_urlBuilder->getUrl('multisafepay/connect/notification/&type=initial', ['_nosid' => true]));
            $redirecturl = str_replace('/admin', '', substr($this->_urlBuilder->getUrl('multisafepay/connect/success', ['_nosid' => true]), 0, -1));
            $cancelurl = str_replace('/admin', '', substr($this->_urlBuilder->getUrl('multisafepay/connect/cancel', ['_nosid' => true]), 0, -1) . '?transactionid=' . $order->getIncrementId());
        } else {
            $notification = $this->_urlBuilder->getUrl('multisafepay/connect/notification/&type=initial', ['_nosid' => true]);
            $redirecturl = substr($this->_urlBuilder->getUrl('multisafepay/connect/success', ['_nosid' => true]), 0, -1);
            $cancelurl = substr($this->_urlBuilder->getUrl('multisafepay/connect/cancel', ['_nosid' => true]), 0, -1) . '?transactionid=' . $order->getIncrementId();
        }

        $msporder = $this->_client->orders->post(array(
            "type" => $type,
            "order_id" => $order->getIncrementId(),
            "currency" => $order->getBaseCurrencyCode(),
            "amount" => $this->getAmountInCents($order),
            "description" => $order->getIncrementId(),
            "var1" => "",
            "var2" => "",
            "var3" => "",
            "items" => $items,
            "manual" => "false",
            "gateway" => $this->_gatewayCode,
            "days_active" => $daysActive,
            "payment_options" => array(
                "notification_url" => $notification,
                "redirect_url" => $redirecturl,
                "cancel_url" => $cancelurl,
                "close_window" => "true"
            ),
            "customer" => array(
                "locale" => $resolver->getLocale(),
                "ip_address" => $order->getRemoteIp(),
                "forwarded_ip" => $order->getXForwardedFor(),
                "first_name" => $billing->getFirstName(),
                "last_name" => $billing->getLastName(),
                "address1" => $street,
                "address2" => $billing->getStreetLine(2),
                "house_number" => $housenumber,
                "zip_code" => $billing->getPostcode(),
                "city" => $billing->getCity(),
                "state" => $billing->getRegion(),
                "country" => $billing->getCountryId(),
                "phone" => $phone,
                "email" => $order->getCustomerEmail(),
            ),
            "plugin" => array(
                "shop" => $magentoInfo->getName() . ' ' . $magentoInfo->getVersion() . ' ' . $magentoInfo->getEdition(),
                "shop_version" => $magentoInfo->getVersion(),
                "plugin_version" => ' - Plugin 1.3.0',
                "partner" => "MultiSafepay",
            ),
            "gateway_info" => array(
                "issuer_id" => !empty($this->issuer_id) ? $this->issuer_id : NULL,
            ),
            "shopping_cart" => $shoppingCart,
            "checkout_options" => $checkoutData,
        ));

        $this->logger->info(print_r($msporder, true));
        $order->addStatusToHistory($order->getStatus(), "User redirected to MultiSafepay" . '<br/>' . "Payment link:" . '<br/>' . $this->_client->orders->getPaymentLink(), false);
        $order->save();
        if ($this->_gatewayCode == "BANKTRANS") {
            $this->banktransurl = substr($this->_urlBuilder->getUrl('multisafepay/connect/success', ['_nosid' => true]), 0, -1) . '?transactionid=' . $order->getIncrementId();
        }

        return $this->_client->orders;
    }

    private function getAmountInCents($order)
    {
        return round($order->getBaseGrandTotal() * 100);
    }

    function getIssuers()
    {
        $environment = $this->getMainConfigData('msp_env');

        $api_key = null;

        if ($environment == true) {
            $this->_client->setApiKey($this->getMainConfigData('test_api_key'));
            $api_key = $this->getMainConfigData('test_api_key');
            $this->_client->setApiUrl('https://testapi.multisafepay.com/v1/json/');
        } else {
            $this->_client->setApiKey($this->getMainConfigData('live_api_key'));
            $api_key = $this->getMainConfigData('live_api_key');
            $this->_client->setApiUrl('https://api.multisafepay.com/v1/json/');
        }

        if (empty($api_key)) {
            return false;
        }

        try {
            $issuers = $this->_client->issuers->get();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__("Error " . htmlspecialchars($e->getMessage())));
        }
        return $issuers;
    }

    public function shipOrder($order)
    {
        $shipped = array();
        $shipped['success'] = false;
        $shipped['error'] = false;
        $payment = $order->getPayment()->getMethodInstance();

        $class = get_class($payment);
        if ($class != 'MultiSafepay\Connect\Model\Connect') {
            return true;
        }


        // check payment method is Klarna or PAD or E-invoice
        if ($payment->_code != 'klarnainvoice' && $payment->_code != 'betaalnaontvangst' && $payment->_code != 'einvoice') {
            return false;
        }

        $environment = $this->getMainConfigData('msp_env');
        if ($environment == true) {
            $this->_client->setApiKey($this->getConfigData('test_api_key', null, $order->getPayment()->getMethodInstance()->_code));
            $this->_client->setApiUrl('https://testapi.multisafepay.com/v1/json/');
        } else {
            $this->_client->setApiKey($this->getConfigData('live_api_key', null, $order->getPayment()->getMethodInstance()->_code));
            $this->_client->setApiUrl('https://api.multisafepay.com/v1/json/');
        }

        $endpoint = 'orders/' . $order->getIncrementId();
        $msporder = $this->_client->orders->patch(
                array(
            "tracktrace_code" => '',
            "carrier" => $order->getShippingDescription(),
            "ship_date" => date('Y-m-d H:i:s'),
            "reason" => 'Shipped'
                ), $endpoint);



        if (!empty($this->_client->orders->success)) {
            $msporder = $this->_client->orders->get($endpoint = 'orders', $order->getIncrementId(), $body = array(), $query_string = false);
            $order->addStatusToHistory($order->getStatus(), __('<b>Klarna Invoice:</b> ') . '<br /><a href="https://online.klarna.com/invoices/' . $this->_client->orders->data->payment_details->external_transaction_id . '.pdf">https://online.klarna.com/invoices/' . $this->_client->orders->data->payment_details->external_transaction_id . '.pdf</a>');
            $order->save();
            $shipped['success'] = true;
            return $shipped;
        } else {
            $shipped['error'] = true;
            return $shipped;
        }
    }

    public function getCheckoutData($order, $productRepo)
    {
        $alternateTaxRates = array();
        $shoppingCart = array();
        $items = $order->getAllItems();

        foreach ($items as $item) {
            $product_id = $item->getProductId();

            foreach ($order->getAllItems() as $order_item) {
                $order_product_id = $order_item->getProductId();
                if ($order_product_id == $product_id) {
                    $quantity = $item->getQtyOrdered();
                }
            }

            if ($item->getParentItem()) {
                continue;
            }
            $taxClass = ($item->getTaxPercent() == 0 ? 'none' : $item->getTaxPercent());
            $rate = $item->getTaxPercent() / 100;

            if ($taxClass == 'none') {
                $rate = '0.00';
            }

            $alternateTaxRates['tax_tables']['alternate'][] = array(
                "standalone" => "true",
                "name" => $taxClass,
                "rules" => array(
                    array("rate" => $rate)
                ),
            );


            $weight = (float) $item->getWeight();
            $product_id = $item->getProductId();

            // name and options
            $itemName = $item->getName();
            $options = $this->getProductOptions($item);
            if (!empty($options)) {
                $optionString = '';
                foreach ($options as $option) {
                    $optionString = $option['label'] . ": " . $option['print_value'] . ",";
                }
                $optionString = substr($optionString, 0, -1);

                $itemName .= ' (';
                $itemName .= $optionString;
                $itemName .= ')';
            }


            $proddata = $productRepo->load($product_id);
            $ndata = $item->getData();

            if ($ndata['price'] != 0) {
                $price = $ndata['price'];
                $tierprices = $proddata->getTierPrice();
                if (count($tierprices) > 0) {
                    $product_tier_prices = (object) $tierprices;
                    $product_price = array();
                    foreach ($product_tier_prices as $key => $value) {
                        $value = (object) $value;
                        if ($item->getQtyOrdered() >= $value->price_qty)
                            if ($ndata['price'] < $value->price) {
                                $price = $ndata['price'];
                            } else {
                                $price = $value->price;
                            }
                        $price = $price;
                    }
                }

                $storeId = $this->getStore();

                // Fix for 1027 with catalog prices including tax
                if ($this->_scopeConfig->getValue('tax/calculation/price_includes_tax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)) {
                    $price = ($item->getRowTotalInclTax() / $item->getQtyOrdered() / (1 + ($item->getTaxPercent() / 100)));
                    $price = round($price, 2);
                }

                $shoppingCart['shopping_cart']['items'][] = array(
                    "name" => $itemName,
                    "description" => $item->getDescription(),
                    "unit_price" => $price,
                    "quantity" => $quantity,
                    "merchant_item_id" => $item->getSku(),
                    "tax_table_selector" => $taxClass,
                    "weight" => array(
                        "unit" => "KG",
                        "value" => $item->getWeight(),
                    )
                );
            }
        }

        //Add shipping line item
        $title = $order->getShippingDescription();

        //Code blow added to recalculate excluding tax for the shipping cost. Older Magento installations round differently, causing a 1 cent mismatch. This is why we recalculate it.
        $diff = $order->getShippingInclTax() - $order->getShippingAmount();
        if ($order->getShippingAmount() > 0) {
            $cost = ($diff / $order->getShippingAmount()) * 100;
        } else {
            $cost = $diff * 100;
        }
        $shipping_percentage = 1 + round($cost, 0) / 100;
        $shippin_exc_tac_calculated = $order->getShippingInclTax() / $shipping_percentage;
        $shipping_percentage = 0 + round($cost, 0) / 100;
        $shipping_cost_orig = $order->getShippingAmount();
        if ($shipping_percentage == 1 || $shipping_cost_orig == 0) {
            $shipping_percentage = "0.00";
        }

        $price = $shippin_exc_tac_calculated;
        $alternateTaxRates['tax_tables']['alternate'][] = array(
            "standalone" => "true",
            "name" => $shipping_percentage,
            "rules" => array(
                array("rate" => $shipping_percentage)
            ),
        );


        $shoppingCart['shopping_cart']['items'][] = array(
            "name" => $title,
            "description" => 'Shipping',
            "unit_price" => $price,
            "quantity" => "1",
            "merchant_item_id" => 'msp-shipping',
            "tax_table_selector" => $shipping_percentage,
            "weight" => array(
                "unit" => "KG",
                "value" => "0",
            )
        );


        //Process discounts
        $discountAmount = $order->getData('base_discount_amount');
        $discountAmountFinal = number_format($discountAmount, 4, '.', '');

        //Add discount line item
        if ($discountAmountFinal != 0) {
            $shoppingCart['shopping_cart']['items'][] = array(
                "name" => $title,
                "description" => 'Discount',
                "unit_price" => $discountAmountFinal,
                "quantity" => "1",
                "merchant_item_id" => 'discount',
                "tax_table_selector" => '0.00',
                "weight" => array(
                    "unit" => "KG",
                    "value" => "0",
                )
            );
            $alternateTaxRates['tax_tables']['alternate'][] = array(
                "standalone" => "true",
                "name" => '0.00',
                "rules" => array(
                    array("rate" => '0.00')
                ),
            );
        }

        $checkoutData["shopping_cart"] = $shoppingCart['shopping_cart'];
        $checkoutData["checkout_options"] = $alternateTaxRates;

        return $checkoutData;
    }

    public function notification($order, $success = false, $fetch = false)
    {
        $params = $this->_requestHttp->getParams();
        $environment = $this->getMainConfigData('msp_env');

        if ($environment == true) {
            $this->_client->setApiKey($this->getConfigData('test_api_key', null, $order->getPayment()->getMethodInstance()->_code));
            $this->_client->setApiUrl('https://testapi.multisafepay.com/v1/json/');
        } else {
            $this->_client->setApiKey($this->getConfigData('live_api_key', null, $order->getPayment()->getMethodInstance()->_code));
            $this->_client->setApiUrl('https://api.multisafepay.com/v1/json/');
        }

        if (isset($params['transactionid'])) {
            $transactionid = $params['transactionid'];
        }

        if (empty($transactionid)) {
            $transactionid = $order->getIncrementId();
        }


        $msporder = $this->_client->orders->get($endpoint = 'orders', $transactionid, $body = array(), $query_string = false);

        $this->logger->info(print_r($msporder, true));

        //Avoid errors shown to consumer when there was an error on requesting the transaction status
        if ($success && !$this->_client->orders->success) {
            return true;
        } elseif (!$this->_client->orders->success) {
            return false;
        }

        $status = $msporder->status;

        /**
         *    Start undo cancel function
         */
        if ($order->getState() == 'canceled' && $status == 'completed') {
            foreach ($order->getItemsCollection() as $item) {
                if ($item->getQtyCanceled() > 0) {
                    $item->setQtyCanceled(0)->save();
                }
            }

            $products = $order->getAllItems();

            if ($this->getGlobalConfig('cataloginventory/options/can_subtract')) {
                $products = $order->getAllItems();
                foreach ($products as $itemId => $product) {
                    $stockItem = $this->_stockInterface->getStockItem($product->getProductId(), null);
                    $stockData = $stockItem->getData();
                    $new = $stockData['qty'] - $product->getQtyOrdered();
                    $stockData['qty'] = $new;
                    $stockItem->setData($stockData);
                    $stockItem->save();
                }
            }


            $order->setBaseDiscountCanceled(0)
                    ->setBaseShippingCanceled(0)
                    ->setBaseSubtotalCanceled(0)
                    ->setBaseTaxCanceled(0)
                    ->setBaseTotalCanceled(0)
                    ->setDiscountCanceled(0)
                    ->setShippingCanceled(0)
                    ->setSubtotalCanceled(0)
                    ->setTaxCanceled(0)
                    ->setTotalCanceled(0);

            $state = 'new';
            $status = 'pending';

            $order->setStatus($status)->setState($state)->save();
            $order->addStatusToHistory($status, 'Order has been reopened because a new transaction was started by the customer!');
            $order->save();
        }

        $payment = $order->getPayment();

        $order_email = $this->getMainConfigData('send_order_email');
        if (($order_email == "after_transaction" && $status != "initialized" && $status != "expired" && !$order->getEmailSent()) ||
                ($payment->getMethodInstance()->_code == 'mspbanktransfer' && !$order->getEmailSent()) ||
                ($status == "expired" && isset($this->_client->orders->data->transaction_id))
        ) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $objectManager->create('Magento\Sales\Model\OrderNotifier')->notify($order);
        }

        /**
         *    ENDING UNDO CANCEL CODE
         */
        switch ($status) {
            case "initialized":
                //We don't process this callback as the status would be the same as the new order status configured.
                break;
            case "completed":
                $order_email = $this->getMainConfigData('send_order_email');

                if ($order_email == "after_transaction_paid" && !$order->getEmailSent()) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $objectManager->create('Magento\Sales\Model\OrderNotifier')->notify($order);
                }

                $this->_registerPaymentCapture(true, $transactionid, $order, $msporder);

                if ($fetch) {
                    return true;
                }

                break;
            case "uncleared":
                if ($fetch) {
                    return false;
                }
                $this->_registerPaymentPending($transactionid, $order, $msporder);
                break;
            case "void":
                if ($fetch) {
                    return false;
                }
                $cancelled = $this->getMainConfigData('cancelled_order_status');
                if ($cancelled != "pending") {
                    $order->registerCancellation('<b>Transaction voided</b><br />')->save();
                } else {
                    $order->setStatus($cancelled)->save();
                }
                break;
            case "declined":
                if ($fetch) {
                    return false;
                }
                $declined = $this->getMainConfigData('declined_order_status');
                if ($declined != "pending") {
                    $order->registerCancellation('<b>Transaction declined</b><br />')->save();
                } else {
                    $order->setStatus($declined)->save();
                }
                break;
            case "expired":
                if ($fetch) {
                    return false;
                }
                $expired = $this->getMainConfigData('expired_order_status');
                if ($expired != "pending") {
                    $order->registerCancellation('<b>Transaction voided</b><br />')->save();
                } else {
                    $order->setStatus($expired)->save();
                }
                $order->registerCancellation('<b>Transaction expired</b><br />')->save();
                break;
            case "cancelled":
                if ($fetch) {
                    return false;
                }
                $cancelled = $this->getMainConfigData('cancelled_order_status');
                if ($cancelled != "pending") {
                    $order->registerCancellation('<b>Transaction voided</b><br />')->save();
                } else {
                    $order->setStatus($cancelled)->save();
                }
                break;
            case "chargeback":
                if ($fetch) {
                    return false;
                }
                $chargeback = $this->getMainConfigData('chargeback_order_status');
                $order->setStatus($chargeback)->save();
                break;
            case "refunded":
                //We don't process this callback as refunds are done using the Magento Backoffice now
                break;
            case "partial_refunded":
                //We don't process this callback as refunds are done using the Magento Backoffice now
                break;
            default:
                return false;
        }

        if (!$fetch) {
            return true;
        }
    }

    /**
     * Process payment pending notification
     *
     * @return void
     * @throws Exception
     */
    public function _registerPaymentPending($transactionid, $order, $msporder)
    {
        $order->getPayment()->setPreparedMessage('<b>Uncleared Transaction you can accept the transaction manually within MultiSafepay Control</b><br />')->setTransactionId($transactionid)
                ->setIsTransactionClosed(
                        0
                )->update(false);
        $order->save();
    }

    /**
     * Process completed payment (either full or partial)
     *
     * @param bool $skipFraudDetection
     * @return void
     */
    protected function _registerPaymentCapture($skipFraudDetection = false, $transactionid, $order, $msporder)
    {
        if ($order->canInvoice() || ($order->getStatus() == "pending_payment" && $msporder->status == "completed")) {
            $payment = $order->getPayment();
            $payment->setTransactionId($msporder->transaction_id);
            $payment->setCurrencyCode($msporder->currency);
            $payment->setPreparedMessage('<b>MultiSafepay status: ' . $msporder->status . '</b><br />');
            $payment->setParentTransactionId($msporder->transaction_id);
            $payment->setShouldCloseParentTransaction(false);
            $payment->setIsTransactionClosed(0);
            $payment->registerCaptureNotification(($msporder->amount / 100), $skipFraudDetection && $msporder->transaction_id);
            $payment->setIsTransactionApproved(true);
            $payment->save();

            if ($payment->getMethodInstance()->_code == 'klarnainvoice') {
                $order->addStatusToHistory($order->getStatus(), "<b>Klarna Reservation number:</b>" . $this->_client->orders->data->payment_details->external_transaction_id, false);
            }

            $order->save();

            //We get the created invoice and send the invoice id to MultiSafepay so it can be added to financial exports
            $environment = $this->getMainConfigData('msp_env');
            if ($environment == true) {
                $this->_client->setApiKey($this->getConfigData('test_api_key', null, $order->getPayment()->getMethodInstance()->_code));
                $this->_client->setApiUrl('https://testapi.multisafepay.com/v1/json/');
            } else {
                $this->_client->setApiKey($this->getConfigData('live_api_key', null, $order->getPayment()->getMethodInstance()->_code));
                $this->_client->setApiUrl('https://api.multisafepay.com/v1/json/');
            }

            foreach ($order->getInvoiceCollection() as $invoice) {
                if ($invoice->getOrderId() == $order->getEntityId()) {
                    $endpoint = 'orders/' . $order->getIncrementId();

                    try {
                        $neworder = $this->_client->orders->patch(
                                array(
                            "invoice_id" => $invoice->getIncrementId(),
                                ), $endpoint);

                        if (!empty($this->_client->orders->result->error_code)) {
                            throw new \Magento\Framework\Exception\LocalizedException(__("Error " . htmlspecialchars($this->_client->orders->result->error_code)));
                        }
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        throw new \Magento\Framework\Exception\LocalizedException(__("Error " . htmlspecialchars($e->getMessage())));
                    }
                }
                $emailInvoice = $this->getMainConfigData('email_invoice');
                $gateway = $payment->getMethodInstance()->_gatewayCode;


                if ($emailInvoice && $gateway != 'PAYAFTER' && $gateway != 'KLARNA') {
                    $this->_invoiceSender->send($invoice, true);
                }/* elseif (($gateway == 'PAYAFTER' || $gateway == 'KLARNA') && $send_bno_invoice && $emailInvoice) {
                  $this->_invoiceSender->send($invoice, true);
                  } */
            }
        }
    }

    /**
     * Check whether payment method can be used
     *
     * @param \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if ($quote == null) {
            $quote = $this->_checkoutSession->getQuote();
        }
        //Check amount restrictions
        if ($quote && (
                $quote->getBaseGrandTotal() < $this->_minAmount || ($this->_maxAmount && $quote->getBaseGrandTotal() > $this->_maxAmount))
        ) {
            return false;
        }


        //Don't show payment method based on main configuration settings
        if ($this->_code == 'connect') {
            return false;
        }


        //Check currency rescrictions
        $allowedCurrencies = explode(',', $this->getConfigData('allowed_currency'));
        if (!in_array($quote->getQuoteCurrencyCode(), $allowedCurrencies)) {
            return false;
        }



        //Check customer group restrictions
        $allowedGroups = explode(',', $this->getConfigData('allowed_groups'));
        if (!in_array($quote->getCustomerGroupId(), $allowedGroups)) {
            return false;
        }


        return parent::isAvailable($quote) && $this->isCarrierAllowed(
                        $quote->getShippingAddress()->getShippingMethod()
        );
    }

    /**
     * Refund
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface|Payment $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {

        $order = $payment->getOrder();

        $environment = $this->getMainConfigData('msp_env');
        if ($environment == true) {
            $this->_client->setApiKey($this->getConfigData('test_api_key', null, $order->getPayment()->getMethodInstance()->_code));
            $this->_client->setApiUrl('https://testapi.multisafepay.com/v1/json/');
        } else {
            $this->_client->setApiKey($this->getConfigData('live_api_key', null, $order->getPayment()->getMethodInstance()->_code));
            $this->_client->setApiUrl('https://api.multisafepay.com/v1/json/');
        }

        $endpoint = 'orders/' . $order->getIncrementId() . '/refunds';
        try {
            $order = $this->_client->orders->post(array(
                "type" => "refund",
                "amount" => $amount * 100,
                "currency" => $order->getBaseCurrencyCode(),
                "description" => "Refund: " . $order->getIncrementId(),
                    ), $endpoint);

            $this->logger->info(print_r($this->_client->orders, true));

            if (!empty($this->_client->orders->result->error_code)) {
                throw new \Magento\Framework\Exception\LocalizedException(__("Error " . htmlspecialchars($this->_client->orders->result->error_code)));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__("Error " . htmlspecialchars($e->getMessage())));
        }
        return $this;
    }

    /**
     * Set order state and status ofter placing order and before redirect to MultiSafepay
     * First status will be pending payment, orders with this status are not visible yet for the consumer
     *
     * @param string $paymentAction
     * @param \Magento\Framework\Object $stateObject
     * @return void
     */
    public function initialize($paymentAction, $stateObject)
    {
        /*
         * Should the order confirmation email be submitted after placing the order?
         */
        $order_email = $this->getMainConfigData('send_order_email');
        if ($order_email != "place_order") {
            $payment = $this->getInfoInstance();
            $order = $payment->getOrder();
            $order->setCanSendNewEmailFlag(false);
        }

        $state = $this->getMainConfigData('order_status');
        $stateObject->setState(\Magento\Sales\Model\Order::STATE_NEW);
        $stateObject->setStatus($state);
        $stateObject->setIsNotified(false);
    }

    //Instructions will be visible within the order/e-mails
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }

    /**
     * Check whether payment method can be used with selected shipping method
     *
     * @param string $shippingMethod
     * @return bool
     */
    public function isCarrierAllowed($shippingMethod)
    {
        if ($this->getConfigData('allowed_carrier_active') == true) {
            if (empty($shippingMethod)) {
                return true;
            }
            return strpos($this->getConfigData('allowed_carrier'), $shippingMethod) !== false;
        } else {
            return true;
        }
    }

    /**
     * Fetch transaction details info
     *
     * Update transaction info if there is one placing transaction only
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param string $transactionId
     * @return boolean
     */
    public function fetchTransactionInfo(\Magento\Payment\Model\InfoInterface $payment, $transactionId)
    {
        $order = $payment->getOrder();
        if ($this->notification($order, false, true)) {
            $payment->setIsTransactionApproved(true);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieve information from gateway/giftcard configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null, $code = null)
    {
        if ('order_place_redirect_url' === $field) {
            return $this->getOrderPlaceRedirectUrl();
        }

        if (null === $storeId) {
            $storeId = $this->getStore();
        }

        if (null === $code) {
            $code = $this->_code;
        }

        $mspType = $this->_mspHelper->getPaymentType($code);
        $path = $mspType . '/' . $code . '/' . $field;


        if ($mspType != 'giftcards' && ($field == "test_api_key" || $field == "live_api_key")) {
            return $this->getMainConfigData($field, $storeId);
        } elseif ($mspType == 'giftcards' && ($field == "test_api_key" || $field == "live_api_key")) {
            if (!empty($this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId))) {
                return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
            } else {
                return $this->getMainConfigData($field, $storeId);
            }
        }
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Retrieve information from multisafepay configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     *
     * @return mixed
     */
    public function getMainConfigData($field, $storeId = null)
    {
        if ('order_place_redirect_url' === $field) {
            return $this->getOrderPlaceRedirectUrl();
        }

        if (null === $storeId) {
            $storeId = $this->getStore();
        }

        $path = 'multisafepay/connect/' . $field;
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getGlobalConfig($path, $storeId = null)
    {

        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    function parseCustomerAddress($street_address)
    {
        list($address, $apartment) = $this->parseAddress($street_address);
        $customer['address'] = $address;
        $customer['housenumber'] = $apartment;
        return $customer;
    }

    /*
     * Parses and splits up an address in street and housenumber
     */

    function parseAddress($street_address)
    {
        $address = $street_address;
        $apartment = "";

        $offset = strlen($street_address);

        while (($offset = $this->rstrpos($street_address, ' ', $offset)) !== false) {
            if ($offset < strlen($street_address) - 1 && is_numeric($street_address[$offset + 1])) {
                $address = trim(substr($street_address, 0, $offset));
                $apartment = trim(substr($street_address, $offset + 1));
                break;
            }
        }

        if (empty($apartment) && strlen($street_address) > 0 && is_numeric($street_address[0])) {
            $pos = strpos($street_address, ' ');

            if ($pos !== false) {
                $apartment = trim(substr($street_address, 0, $pos), ", \t\n\r\0\x0B");
                $address = trim(substr($street_address, $pos + 1));
            }
        }

        return array($address, $apartment);
    }

    // From http://www.php.net/manual/en/function.strrpos.php#78556
    function rstrpos($haystack, $needle, $offset = null)
    {
        $size = strlen($haystack);

        if (is_null($offset)) {
            $offset = $size;
        }

        $pos = strpos(strrev($haystack), strrev($needle), $size - $offset);

        if ($pos === false) {
            return false;
        }

        return $size - $pos - strlen($needle);
    }

}
