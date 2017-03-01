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

class Fastcheckout extends \Magento\Payment\Model\Method\AbstractMethod {

    protected $_isInitializeNeeded = true;
    protected $_infoBlockType = 'Magento\Payment\Block\Info\Instructions';
    public $issuer_id = null;

    /**
     * @var string
     */
    protected $_code = 'fastcheckout';

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
    public $_manualGateway =null;
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
    ) {
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
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/multisafepay.log');
		$this->logger = new \Zend\Log\Logger();
		$this->logger->addWriter($writer);
        
    }

    public function transactionRequest($session, $productRepo = null) {
		$quote = $session->getQuote();
		$quoteId = $quote->getId();
		
        $environment = $this->getMainConfigData('msp_env');
  
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $magentoInfo = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');

        if ($environment == true) {
            $this->_client->setApiKey($this->getConfigData('test_api_key', null, null));
            $this->_client->setApiUrl('https://testapi.multisafepay.com/v1/json/');
        } else {
            $this->_client->setApiKey($this->getConfigData('live_api_key', null, null));
            $this->_client->setApiUrl('https://api.multisafepay.com/v1/json/');
        }

        $checkoutData = $this->getCheckoutData($quote, $productRepo);
        $shoppingCart = $checkoutData["shopping_cart"];
        $checkoutData = $checkoutData["checkout_options"];
        $type = 'checkout';
        
        
	    $notification = $this->_urlBuilder->getUrl('multisafepay/fastcheckout/notification/&type=initial', ['_nosid' => true]);
	    $redirecturl = substr($this->_urlBuilder->getUrl('multisafepay/fastcheckout/success', ['_nosid' => true]), 0, -1);
	    $cancelurl = substr($this->_urlBuilder->getUrl('multisafepay/fastcheckout/cancel', ['_nosid' => true]), 0, -1) . '?transactionid=' . $quoteId;

        $msporder = $this->_client->orders->post(array(
            "type" => $type,
            "order_id" => $quoteId,
            "currency" => 'EUR',
            "amount" => intval((string) ($quote->getBaseGrandTotal() * 100)),
            "description" => 'Quote: '.$quoteId ,
            "var1" => "",
            "var2" => "",
            "var3" => "",
            "items" => "",
            "manual" => "false",
            "gateway" => $this->_gatewayCode,
            "days_active" => '30',
            "payment_options" => array(
                "notification_url" => $notification,
                "redirect_url" => $redirecturl,
                "cancel_url" => $cancelurl,
                "close_window" => "true"
            ),
            "plugin" => array(
                "shop" => $magentoInfo->getName() . ' ' . $magentoInfo->getVersion() . ' ' . $magentoInfo->getEdition(),
                "shop_version" => $magentoInfo->getVersion(),
                "plugin_version" => ' - Plugin 1.1.0',
                "partner" => "MultiSafepay",
            ),
            "shopping_cart" => $shoppingCart,
            "checkout_options" => $checkoutData,
        ));
		
		$this->logger->info(print_r($msporder, true));
       
        return $this->_client->orders;
    }


    public function getCheckoutData($order, $productRepo) {
        $alternateTaxRates = array();
        $shoppingCart = array();
        $items = $order->getAllItems();

        foreach ($items as $item) {
            $product_id = $item->getProductId();

            foreach ($order->getAllItems() as $order_item) {
                $order_product_id = $order_item->getProductId();
                if ($order_product_id == $product_id) {
                    $quantity = $item->getQty();
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
        $checkoutData["checkout_options"]["use_shipping_notification"] = true;

        return $checkoutData;
    }
    

    public function notification($params) {


		$orderData=array(
'currency_id'  => 'EUR',
'email'        => 'ruud@multisafepay.com', //buyer email id
'shipping_address' =>array(
    'firstname'    => 'jhon', //address Details
    'lastname'     => 'Deo',
    'street' => 'xxxxx',
    'city' => 'xxxxx',
    'country_id' => 'IN',
    'region' => 'xxx',
    'postcode' => '43244',
    'telephone' => '52332',
    'fax' => '32423',
    'save_in_address_book' => 1
));
	    
	    
	    $order_id =$this->_mspHelper->createOrder($orderData);
	    echo $order_id;
	    exit;





	    $params = $this->_requestHttp->getParams();
        $environment = $this->getMainConfigData('msp_env');

        if ($environment == true) {
            $this->_client->setApiKey($this->getConfigData('test_api_key', null, $order->getPayment()->getMethodInstance()->_code));
            $this->_client->setApiUrl('https://testapi.multisafepay.com/v1/json/');
        } else {
            $this->_client->setApiKey($this->getConfigData('live_api_key', null, $order->getPayment()->getMethodInstance()->_code));
            $this->_client->setApiUrl('https://api.multisafepay.com/v1/json/');
        }


        $transactionid = $params['transactionid'];
        $msporder = $this->_client->orders->get($endpoint = 'orders', $transactionid, $body = array(), $query_string = false);

		$this->logger->info(print_r($msporder, true));

        //Avoid errors shown to consumer when there was an error on requesting the transaction status
        if ($success && !$this->_client->orders->success) {
            return true;
        } elseif (!$this->_client->orders->success) {
            return false;
        }

        $status = $msporder->status;

        switch ($status) {
            case "initialized":
                //We don't process this callback as the status would be the same as the new order status configured.
                break;
            case "completed":
                $order_email = $this->getMainConfigData('send_order_email');

                if ($order_email = "after_transaction_paid" && !$order->getEmailSent()) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $objectManager->create('Magento\Sales\Model\OrderNotifier')->notify($order);
                }

                $this->_registerPaymentCapture(true, $transactionid, $order, $msporder);
                break;
            case "uncleared":
                $this->_registerPaymentPending($transactionid, $order, $msporder);
                break;
            case "void":
            	$cancelled = $this->getMainConfigData('cancelled_order_status');
            	if($cancelled != "pending"){
                	$order->registerCancellation('<b>Transaction voided</b><br />')->save();
                }else{
	                $order->setStatus($cancelled)->save();
                }
                break;
            case "declined":
            	$declined = $this->getMainConfigData('declined_order_status');
            	if($declined != "pending"){
                	$order->registerCancellation('<b>Transaction declined</b><br />')->save();
                }else{
	                $order->setStatus($declined)->save();
                }
                break;
            case "expired":
            	$expired = $this->getMainConfigData('expired_order_status');
            	if($expired != "pending"){
                	$order->registerCancellation('<b>Transaction voided</b><br />')->save();
                }else{
	                $order->setStatus($expired)->save();
                }
                $order->registerCancellation('<b>Transaction expired</b><br />')->save();
                break;
            case "cancelled":
                $cancelled = $this->getMainConfigData('cancelled_order_status');
            	if($cancelled != "pending"){
                	$order->registerCancellation('<b>Transaction voided</b><br />')->save();
                }else{
	                $order->setStatus($cancelled)->save();
                }
                break;
            case "chargeback":
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

        return true;
    }

    /**
     * Process payment pending notification
     *
     * @return void
     * @throws Exception
     */
    public function _registerPaymentPending($transactionid, $order, $msporder) {
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
    protected function _registerPaymentCapture($skipFraudDetection = false, $transactionid, $order, $msporder) {
        if ($order->canInvoice() ||($order->getStatus() == "pending_payment" && $msporder->status == "completed")) {
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
     * Refund
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface|Payment $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount) {

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

   
    //Instructions will be visible within the order/e-mails
    public function getInstructions() {
        return trim($this->getConfigData('instructions'));
    }


    /**
     * Retrieve information from gateway/giftcard configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null, $code = null) {
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
    public function getMainConfigData($field, $storeId = null) {
        if ('order_place_redirect_url' === $field) {
            return $this->getOrderPlaceRedirectUrl();
        }

        if (null === $storeId) {
            $storeId = $this->getStore();
        }

        $path = 'multisafepay/connect/' . $field;
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getGlobalConfig($path, $storeId = null) {

        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    function parseCustomerAddress($street_address) {
        list($address, $apartment) = $this->parseAddress($street_address);
        $customer['address'] = $address;
        $customer['housenumber'] = $apartment;
        return $customer;
    }
    
    
    public function getShippingRates($params){
	    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $quote = $objectManager->create('\Magento\Quote\Model\Quote')->loadByIdWithoutStore($params['transactionid']);
        
  
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCountryId($params['countrycode']);
        $shippingAddress->setPostcode($params['zipcode']);
        $shippingAddress->setCollectShippingRates(true);

        $rates = $shippingAddress->collectShippingRates()->getGroupedAllShippingRates();

        foreach ($rates as $carrier) {
            foreach ($carrier as $rate) {
                $shipping = array();
                $shipping['id'] = $rate->getCode();
                $shipping['name'] = $rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle();
                $shipping['cost'] = number_format($rate->getPrice(), 2, '.', '');
                $shipping['currency'] = 'EUR';

                $output[] = $shipping;
            }
        }
        
        $outxml = '<?xml version="1.0" encoding="UTF-8"?>';
        $outxml .= '<shipping-info>';
        foreach ($output as $rate) {
            $outxml .= '<shipping>';
            $outxml .= '<shipping-name>' . htmlentities($rate['name']) . '</shipping-name>';
            $outxml .= '<shipping-cost currency="' . $rate['currency'] . '">' . $rate['cost'] . '</shipping-cost>';
            $outxml .= '<shipping-id>' . $rate['id'] . '</shipping-id>';
            $outxml .= '</shipping>';
        }
        $outxml .= '</shipping-info>';

        return $outxml;
    }

}

