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
    public $customerFactory;
    public $cartManagementInterface;
    public $cartRepositoryInterface;    
    public $customerRepository;
    public $_productFactory;
    public $shippingRate;
    public $_objectManager;
    public $quote;
    public $quoteManagement;
    public $orderService;

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
    \Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory, \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory, \Magento\Payment\Helper\Data $paymentData, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Payment\Model\Method\Logger $logger, \Magento\Framework\Module\ModuleListInterface $moduleList, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Checkout\Model\Session $checkoutSession, \Magento\Framework\UrlInterface $urlBuilder, \Magento\Framework\App\RequestInterface $requestHttp, \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null, \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,  array $data = []
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
        $this->_minAmount = $this->getMainConfigData('min_order_total');
        $this->_maxAmount = $this->getMainConfigData('max_order_total');
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/multisafepay.log');
		$this->logger = new \Zend\Log\Logger();
		$this->logger->addWriter($writer);
		 $this->_client->logger = $this->logger;
        
    }

    public function transactionRequest($session, $productRepo = null) {
		$quote = $session->getQuote();
		$quoteId = $quote->getId();
		
        $environment = $this->getConnectConfigData('msp_env');
  
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $magentoInfo = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');

        if ($environment == true) {
            $this->_client->setApiKey($this->getConnectConfigData('test_api_key', null, null));
            $this->_client->setApiUrl('https://testapi.multisafepay.com/v1/json/');
        } else {
            $this->_client->setApiKey($this->getConnectConfigData('ive_api_key', null, null));
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
		
		//$this->logger->info(print_r($msporder, true));
       
        return $this->_client->orders;
    }


    public function getCheckoutData($order, $productRepo) {
        $alternateTaxRates = array();
        $shoppingCart = array();
        $items = $order->getAllItems();
        
        /*
	    * Get tax rates for shippingmethod, this will be used to set a default Tax Rate
	    */
        $store= $this->getStore();
        $shipping_tax_id = $this->getGlobalConfig('tax/classes/shipping_tax_class');
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_taxModelConfig = $objectManager->get('Magento\Tax\Model\Calculation\Rate');
        $taxRates = $_taxModelConfig->getCollection()->getData();
        $taxArray = array();
        foreach ($taxRates as $tax) {
	        if($tax['tax_calculation_rate_id'] == $shipping_tax_id){
		        
	       
	        
            $taxRateId = $tax['tax_calculation_rate_id'];
            $taxCode = $tax["code"];
            $taxRate = $tax["rate"];
            $taxName = $taxCode.'('.$taxRate.'%)';
   
            $alternateTaxRates['tax_tables']['default'][] = array(
                "shpping_taxed" => "true",
                "name" => $taxName,
                "rules" => array(
                    array("rate" => $taxRate/100)
                ),
            );
            
            }
        }

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
                    "merchant_item_id" => $item->getProductId(),
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
	    
	  	$environment = $this->getConnectConfigData('msp_env');
        if ($environment == true) {
            $this->_client->setApiKey($this->getConnectConfigData('test_api_key', null, null));
            $this->_client->setApiUrl('https://testapi.multisafepay.com/v1/json/');
        } else {
            $this->_client->setApiKey($this->getConnectConfigData('live_api_key', null, null));
            $this->_client->setApiUrl('https://api.multisafepay.com/v1/json/');
        }

        if (isset($params['transactionid'])) {
            $transactionid = $params['transactionid'];
        }

        
        $msporder = $this->_client->orders->get($endpoint = 'orders', $transactionid, $body = array(), $query_string = false);
	    $cart = $msporder->shopping_cart->items;
	    
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->customerFactory = $this->_objectManager->get('Magento\Customer\Model\CustomerFactory');
        $this->cartManagementInterface = $this->_objectManager->get('Magento\Quote\Api\CartManagementInterface');
        $this->cartRepositoryInterface = $this->_objectManager->get('Magento\Quote\Api\CartRepositoryInterface');
        $this->customerRepository = $this->_objectManager->get('Magento\Customer\Api\CustomerRepositoryInterface');
        $this->_productFactory = $this->_objectManager->get('Magento\Catalog\Model\ProductFactory');
        $this->shippingRate = $this->_objectManager->get('Magento\Quote\Model\Quote\Address\Rate');
		$this->quote = $this->_objectManager->get('Magento\Quote\Model\QuoteFactory');
		$this->quoteManagement = $this->_objectManager->get('Magento\Quote\Model\QuoteManagement');
		$this->orderService = $this->_objectManager->get('Magento\Sales\Model\Service\OrderService');
 
	    $created= $this->createOrder($msporder);

        return $created;
    }
    
    
    
    
    /**
     * Create Order On Your Store
     *
     * @param array $orderData
     * @return int $orderId
     *
     */
    public function createOrder($orderData) {
		$billing_address = array(
			'firstname'    => $orderData->customer->first_name, //address Details
		    'lastname'     => $orderData->customer->last_name,
		    'street' => $orderData->customer->address1.' '.$orderData->customer->house_number,
		    'city' => $orderData->customer->city,
		    'country_id' => $orderData->customer->country,
		    'region' => '',
		    'postcode' => $orderData->customer->zip_code,
		    'telephone' => ($orderData->customer->phone1)?$orderData->customer->phone1:'0000000000',
		    'email' => $orderData->customer->email,
		    'fax' => '',
		    'save_in_address_book' => 1
		);
		
		
		$shipping_address = array(
			'firstname'    => $orderData->delivery->first_name, //address Details
		    'lastname'     => $orderData->delivery->last_name,
		    'street' => $orderData->delivery->address1.' '.$orderData->delivery->house_number,
		    'city' => $orderData->delivery->city,
		    'country_id' => $orderData->delivery->country,
		    'region' => '',
		    'postcode' => $orderData->delivery->zip_code,
		    'telephone' => ($orderData->delivery->phone1)?$orderData->delivery->phone1:'0000000000',
		    'fax' => '',
		    'email' => $orderData->customer->email,
		    'save_in_address_book' => 1
		);
		

	    $quote_id = $orderData->order_id;
	
	    /** A QUOTE HAS ALREADY BEEN CREADED WHEN USING FASTCHECKOUT, SO THIS CODE SHOULD NOT BE USED. IT CAN BE USED FOR QWINDO
		    
		    
        //init the store id and website id @todo pass from array
        $store = $this->_storeManager->getStore();
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        //init the customer
        $customer=$this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($orderData->customer->email);// load customer by email address
        $passwordLength = 10;
        //check the customer
        if(!$customer->getEntityId()){
            //If not avilable then create this customer
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname($orderData->customer->first_name)
                ->setLastname($orderData->customer->last_name)
                ->setEmail($orderData->customer->email)
                ->setPassword($this->randomPassword($passwordLength));
            $customer->save();
			$customer->sendNewAccountEmail();
            
        }
        //init the quote
        $cart_id = $this->cartManagementInterface->createEmptyCart(); //We can do this for Qwindo, for FCO a quote already exists so we don't need to create a new empty one.
        $cart = $this->cartRepositoryInterface->get($cart_id);
        $cart->setStore($store);
        // if you have already buyer id then you can load customer directly
        $customer= $this->customerRepository->getById($customer->getEntityId());
        $cart->setCurrency();
        $cart->assignCustomer($customer); //Assign quote to customer
        //add items in quote
        foreach($orderData->shopping_cart->items as $item){
	 
            $product = $this->_productFactory->create()->load($item->merchant_item_id);
            $cart->addProduct(
                $product,
                intval($item->quantity)
            );
        }
        //Set Address to quote @todo add section in order data for seperate billing and handle it
        $cart->getBillingAddress()->addData($billing_address);
        $cart->getShippingAddress()->addData($shipping_address);
        // Collect Rates and Set Shipping & Payment Method
        $this->shippingRate
            ->setCode('freeshipping_freeshipping')
            ->getPrice(1);
        $shippingAddress = $cart->getShippingAddress();
        //@todo set in order data
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod('flatrate_flatrate'); //shipping method
        $cart->getShippingAddress()->addShippingRate($this->shippingRate);
        $cart->setPaymentMethod(strtolower($orderData->payment_details->type)); //payment method
        //@todo insert a variable to affect the invetory
        $cart->setInventoryProcessed(false);
        // Set sales order payment
        $cart->getPayment()->importData(['method' => strtolower($orderData->payment_details->type)]);
        // Collect total and saeve
        $cart->collectTotals();
        // Submit the quote and create the order
        $cart->save();
        $cart = $this->cartRepositoryInterface->get($cart->getId());
        $order_id = $this->cartManagementInterface->placeOrder($cart->getId());
        
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($order_id);
        
        $this->_objectManager->create('Magento\Sales\Model\OrderNotifier')->notify($order);
        return $order_id;
        
        **/
        
        
        
        $store=$this->_storeManager->getStore();
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $customer=$this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($orderData->customer->email);// load customet by email address
        $passwordLength = 10;
        if(!$customer->getEntityId()){
            //If not avilable then create this customer 
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname($orderData->customer->first_name)
                ->setLastname($orderData->customer->last_name)
                ->setEmail($orderData->customer->email)
                ->setPassword($this->randomPassword($passwordLength));
            $customer->save();
            $customer->sendNewAccountEmail();
        }
        
        //$quote= $this->quote->create(); //Create object of quote
        $quote= $this->quote->create()->load($quote_id);
        $quote->setStore($store); //set store for which you create quote
        // if you have allready buyer id then you can load customer directly 
        $customer= $this->customerRepository->getById($customer->getEntityId());
        //$quote->setCurrency();
        $quote->assignCustomer($customer); //Assign quote to customer
 
        //add items in quote
        foreach($orderData->shopping_cart->items as $item){
	 
            $product = $this->_productFactory->create()->load($item->merchant_item_id);
            //$quote->addProduct($product,intval($item->quantity));
        }
 
        //Set Address to quote
        $quote->getBillingAddress()->addData($billing_address);
        $quote->getShippingAddress()->addData($shipping_address);
 
        // Collect Rates and Set Shipping & Payment Method
 
        $shippingAddress=$quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
                        ->collectShippingRates()
                        ->setShippingMethod('flatrate_flatrate'); //shipping method TODO load based on transaction data
                               
                        
                        
        $quote->setPaymentMethod(strtolower($orderData->payment_details->type)); //payment method
        //$quote->setInventoryProcessed(false); //not effetc inventory
        $quote->save(); //Now Save quote and your quote is ready
 
        // Set Sales Order Payment
        $quote->getPayment()->importData(['method' => strtolower($orderData->payment_details->type)]);
 
        // Collect Totals & Save Quote
        $quote->collectTotals()->save();
 
 
		//Here we need to detect if the order has already been created, if so then we can don't need to do anything and return.
		// check if an order is already created        
		$ordercollection = $this->_objectManager->create('Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
		$collection = $ordercollection->create()->addAttributeToFilter('quote_id', $quote_id);
		if (count($collection)) {
            foreach($collection as $order){
	            return $order->getId();
            }
            
        }
 
        // Create Order From Quote
        $order = $this->quoteManagement->submit($quote);
        
        $order_model = $this->_objectManager->create('Magento\Sales\Model\Order')->load($order->getId());
        
        $this->_objectManager->create('Magento\Sales\Model\OrderNotifier')->notify($order_model);
        $order->setEmailSent(1);
        $order->save();

        return $order->getId();
 
    }


	/*
	* This function generates a password as the generatePassword function from Magento is no longer availble for the customer object
	*
	*/
	function randomPassword( $length = 8 ) 
	{ 
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?"; 
	$length = rand(10, 16); 
	$password = substr( str_shuffle(sha1(rand() . time()) . $chars ), 0, $length );
	 return $password;
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

     //Instructions will be visible within the order/e-mails
    public function getInstructions() {
        return trim($this->getMainConfigData('instructions'));
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

        $path = 'fastcheckout/fastcheckout/' . $field;
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
    public function getConnectConfigData($field, $storeId = null) {
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

