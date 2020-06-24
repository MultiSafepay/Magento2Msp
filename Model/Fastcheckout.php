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
 * @author      MultiSafepay <integration@multisafepay.com>
 * @copyright   Copyright (c) MultiSafepay, Inc. (https://www.multisafepay.com)
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

use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Session;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\Collection as TableRateCollection;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Address\Rate as AdressRate;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderNotifier;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Service\OrderService;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Calculation\Rate as TaxRate;
use MultiSafepay\Connect\Helper\Data as MspHelperData;
use MultiSafepay\Connect\Model\Api\MspClient;
use Magento\Framework\AppInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;

class Fastcheckout extends \Magento\Payment\Model\Method\AbstractMethod
{

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
    protected $_productMetadataInterface;
    protected $tableRateCollection;
    protected $_taxRate;
    protected $_order;
    protected $cacheTypeList;
    protected $cacheFrontendPool;
    protected $orderCollection;
    protected $orderNotifier;
    public $_invoiceSender;
    public $_stockInterface;
    public $banktransurl;
    protected $logger;
    protected $resourceConfig;
    protected $_objectManager;
    public $_manualGateway = null;
    public $_isAdmin = false;
    public $customerFactory;
    public $cartManagementInterface;
    public $cartRepositoryInterface;
    public $customerRepository;
    public $_productFactory;
    public $shippingRate;
    public $quote;
    public $quoteManagement;
    public $orderService;
    public $scopeConfig;
    public $shippingmethods = [
        "pickup" => "pickup_store",
        "flatrate" => "flatrate_flatrate",
        "freeshipping" => "freeshipping_freeshipping",
        "bestway" => "tablerate_bestway"
    ];

    /**
     * @param \Magento\Framework\Model\Context                                          $context
     * @param \Magento\Framework\Registry                                               $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory                         $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory                              $customAttributeFactory
     * @param \Magento\Payment\Helper\Data                                              $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface                        $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger                                      $logger
     * @param \Magento\Framework\Module\ModuleListInterface                             $moduleList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface                      $localeDate
     * @param \Magento\Store\Model\StoreManagerInterface                                $storeManager
     * @param \Magento\Framework\UrlInterface                                           $urlBuilder
     * @param \Magento\Framework\App\RequestInterface                                   $requestHttp
     * @param \Magento\Framework\App\ProductMetadataInterface                           $productMetadataInterface
     * @param \Magento\Tax\Model\Calculation\Rate                                       $taxRate
     *
     * @param \Magento\Customer\Model\CustomerFactory                                   $customerFactory
     * @param \Magento\Quote\Api\CartManagementInterface                                $cartManagementInterface
     * @param \Magento\Quote\Api\CartRepositoryInterface                                $cartRepositoryInterface
     * @param \Magento\Customer\Api\CustomerRepositoryInterface                         $customerRepositoryInterface
     * @param \Magento\Catalog\Model\ProductFactory                                     $productFactory
     * @param \Magento\Quote\Model\Quote\Address\Rate                                   $adressRate
     * @param \Magento\Quote\Model\QuoteFactory                                         $quoteFactory
     * @param \Magento\Quote\Model\QuoteManagement                                      $quoteManagement
     * @param \Magento\Sales\Model\Service\OrderService                                 $orderService
     * @param \Magento\Framework\App\Cache\TypeListInterface                            $cacheTypeList
     * @param \Magento\Framework\App\Cache\Frontend\Pool                                $cacheFrontendPool
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory                $collectionFactory
     * @param \Magento\Sales\Model\Order                                                $order
     * @param \Magento\Sales\Model\OrderNotifier                                        $orderNotifier
     * @param \Magento\Config\Model\ResourceModel\Config                                $resourceConfig
     * @param \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\Collection $tableRateCollection
     *
     * @param \MultiSafepay\Connect\Model\Api\MspClient                                 $mspClient
     * @param \MultiSafepay\Connect\Helper\Data                                         $helperData
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource                   $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb                             $resourceCollection
     * @param array                                                                     $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        ModuleListInterface $moduleList,
        TimezoneInterface $localeDate,
        StoreManagerInterface $storeManager,
        Session $checkoutSession,
        UrlInterface $urlBuilder,
        RequestInterface $requestHttp,
        ProductMetadataInterface $productMetadataInterface,
        TaxRate $taxRate,
        CustomerFactory $customerFactory,
        CartManagementInterface $cartManagementInterface,
        CartRepositoryInterface $cartRepositoryInterface,
        CustomerRepositoryInterface $customerRepositoryInterface,
        ProductFactory $productFactory,
        AdressRate $adressRate,
        QuoteFactory $quoteFactory,
        QuoteManagement $quoteManagement,
        OrderService $orderService,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool,
        CollectionFactory $collectionFactory,
        Order $order,
        OrderNotifier $orderNotifier,
        Config $resourceConfig,
        TableRateCollection $tableRateCollection,
        MspClient $mspClient,
        MspHelperData $helperData,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger
        );

        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->cacheTypeList = $cacheTypeList;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->_urlBuilder = $urlBuilder;
        $this->_client = $mspClient;
        $this->_requestHttp = $requestHttp;
        $this->_mspHelper = $helperData;
        $this->_taxRate = $taxRate;
        $this->_minAmount = $this->getMainConfigData('min_order_total');
        $this->_maxAmount = $this->getMainConfigData('max_order_total');
        $this->scopeConfig = $scopeConfig;

        $this->customerFactory = $customerFactory;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->customerRepository = $customerRepositoryInterface;
        $this->_productFactory = $productFactory;
        $this->shippingRate = $adressRate;
        $this->quote = $quoteFactory;
        $this->quoteManagement = $quoteManagement;
        $this->orderService = $orderService;
        $this->orderCollection = $collectionFactory;
        $this->_order = $order;
        $this->orderNotifier = $orderNotifier;
        $this->resourceConfig = $resourceConfig;
        $this->_productMetadataInterface = $productMetadataInterface;
        $this->tableRateCollection = $tableRateCollection;

        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/multisafepay.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        $this->_client->logger = $this->logger;
        $this->_client->debug = ($this->getConnectConfigData('msp_debug')) ? true : false;
    }

    public function transactionRequest($session, $resetGateway = false)
    {
        $quote = $session->getQuote();
        $quoteId = $quote->getId();

        $environment = $this->getConnectConfigData('msp_env');

        $magentoInfo = $this->_productMetadataInterface;

        if ($environment == true) {
            $this->_client->setApiKey($this->getConnectConfigData('test_api_key', null, null));
            $this->_client->setApiUrl('https://testapi.multisafepay.com/v1/json/');
        } else {
            $this->_client->setApiKey($this->getConnectConfigData('live_api_key', null, null));
            $this->_client->setApiUrl('https://api.multisafepay.com/v1/json/');
        }

        $checkoutData = $this->getCheckoutData($quote);
        $shoppingCart = $checkoutData["shopping_cart"];
        $checkoutData = $checkoutData["checkout_options"];
        $type = 'checkout';


        $notification = $this->_urlBuilder->getUrl('multisafepay/fastcheckout/notification/&type=initial', ['_nosid' => true]);
        $redirecturl = substr($this->_urlBuilder->getUrl('multisafepay/fastcheckout/success', ['_nosid' => true]), 0, -1);
        $cancelurl = substr($this->_urlBuilder->getUrl('multisafepay/fastcheckout/cancel', ['_nosid' => true]), 0, -1) . '?hash=' . $this->_mspHelper->encryptOrder($quoteId);

        $msporder = $this->_client->orders->post([
            "type" => $type,
            "order_id" => $quoteId,
            "currency" => 'EUR',
            "amount" => intval((string) ($quote->getBaseGrandTotal() * 100)),
            "description" => 'Quote: ' . $quoteId,
            "var1" => "",
            "var2" => "",
            "var3" => "",
            "items" => "",
            "manual" => "false",
            "gateway" => $this->_gatewayCode,
            "days_active" => '30',
            "payment_options" => [
                "notification_url" => $notification,
                "redirect_url" => $redirecturl,
                "cancel_url" => $cancelurl,
                "close_window" => "true"
            ],
            "plugin" => [
                "shop" => $magentoInfo->getName() . ' ' . $magentoInfo->getVersion() . ' ' . $magentoInfo->getEdition(),
                "shop_version" => $magentoInfo->getVersion(),
                "plugin_version" => ' - Plugin 1.12.1',
                "partner" => "MultiSafepay",
            ],
            "shopping_cart" => $shoppingCart,
            "checkout_options" => $checkoutData,
        ]);

        return $this->_client->orders;
    }

    public function getCheckoutData($order)
    {
        $alternateTaxRates = [];
        $shoppingCart = [];
        $items = $order->getAllItems();

        /*
         * Get tax rates for shippingmethod, this will be used to set a default Tax Rate
         */
        $store = $this->getStore();
        $shipping_tax_id = $this->getGlobalConfig('tax/classes/shipping_tax_class');

        $_taxModelConfig = $this->_taxRate;
        $taxRates = $_taxModelConfig->getCollection()->getData();
        $taxArray = [];
        foreach ($taxRates as $tax) {
            if ($tax['tax_calculation_rate_id'] == $shipping_tax_id) {
                $taxRateId = $tax['tax_calculation_rate_id'];
                $taxCode = $tax["code"];
                $taxRate = $tax["rate"];
                $taxName = $taxCode . '(' . $taxRate . '%)';

                $alternateTaxRates['tax_tables']['default'][] = [
                    "shipping_taxed" => "true",
                    "name" => $taxName,
                    "rules" => [
                        ["rate" => $taxRate / 100]
                    ],
                ];
            }
        }

        foreach ($items as $item) {
            $product_id = $item->getProductId();

            foreach ($order->getAllItems() as $order_item) {
                $order_product_id = $order_item->getProductId();
                if ($order_product_id == $product_id) {
                    $quantity = (string) floatval($item->getQty());
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


            $alternateTaxRates['tax_tables']['alternate'][] = [
                "standalone" => "true",
                "name" => $taxClass,
                "rules" => [
                    ["rate" => $rate]
                ],
            ];

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

            $ndata = $item->getData();

            if ($ndata['price'] != 0) {
                $price = $ndata['price'] - ($item->getDiscountAmount() / $quantity);

                $storeId = $this->getStore();

                // Fix for 1027 with catalog prices including tax
                if ($this->_scopeConfig->getValue('tax/calculation/price_includes_tax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)) {
                    $price = (($item->getRowTotalInclTax() - $item->getDiscountAmount()) / $quantity / (1 + ($item->getTaxPercent() / 100)));
                    $price = round($price, 2);
                }

                $shoppingCart['shopping_cart']['items'][] = [
                    "name" => $itemName,
                    "description" => $item->getDescription(),
                    "unit_price" => $price,
                    "quantity" => $quantity,
                    "merchant_item_id" => $item->getProductId(),
                    "tax_table_selector" => $taxClass,
                    "weight" => [
                        "unit" => "KG",
                        "value" => $item->getWeight(),
                    ]
                ];
            }
        }

        $checkoutData["shopping_cart"] = $shoppingCart['shopping_cart'];
        $checkoutData["checkout_options"] = $alternateTaxRates;
        $checkoutData["checkout_options"]["use_shipping_notification"] = true;

        return $checkoutData;
    }

    public function notification($params)
    {

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


        $msporder = $this->_client->orders->get($endpoint = 'orders', $transactionid, $body = [], $query_string = false);

        if (!empty(json_decode(json_encode($msporder), true))) {
            $cart = $msporder->shopping_cart->items;



            $created = $this->createOrder($msporder);

            return $created;
        }
        return false;
    }

    /**
     * Create Order On Your Store
     *
     * @param array $orderData
     * @return int $orderId
     *
     */
    public function createOrder($orderData)
    {
        $billing_address = [
            'firstname' => $orderData->customer->first_name, //address Details
            'lastname' => $orderData->customer->last_name,
            'street' => $orderData->customer->address1 . ' ' . $orderData->customer->house_number,
            'city' => $orderData->customer->city,
            'country_id' => $orderData->customer->country,
            'region' => '',
            'postcode' => $orderData->customer->zip_code,
            'telephone' => ($orderData->customer->phone1) ? $orderData->customer->phone1 : '0000000000',
            'email' => $orderData->customer->email,
            'fax' => '',
            'save_in_address_book' => 0
        ];


        $shipping_address = [
            'firstname' => $orderData->delivery->first_name, //address Details
            'lastname' => $orderData->delivery->last_name,
            'street' => $orderData->delivery->address1 . ' ' . $orderData->delivery->house_number,
            'city' => $orderData->delivery->city,
            'country_id' => $orderData->delivery->country,
            'region' => '',
            'postcode' => $orderData->delivery->zip_code,
            'telephone' => ($orderData->delivery->phone1) ? $orderData->delivery->phone1 : '0000000000',
            'fax' => '',
            'email' => $orderData->customer->email,
            'save_in_address_book' => 0
        ];


        $quote_id = $orderData->order_id;

        /** A QUOTE HAS ALREADY BEEN CREATED WHEN USING FASTCHECKOUT, SO THIS CODE SHOULD NOT BE USED. IT CAN BE USED FOR QWINDO


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

         * */
        $store = $this->_storeManager->getStore();
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($orderData->customer->email); // load customer by email address
        $passwordLength = 10;
        if (!$customer->getEntityId()) {
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
        $quote = $this->quote->create()->load($quote_id);
        $quote->setStore($store); //set store for which you create quote
        // if you have allready buyer id then you can load customer directly
        $customer = $this->customerRepository->getById($customer->getEntityId());
        $quote->setCurrency();
        $quote->assignCustomer($customer); //Assign quote to customer
        //add items in quote
        foreach ($orderData->shopping_cart->items as $item) {
            $product = $this->_productFactory->create()->load($item->merchant_item_id);
            //$quote->addProduct($product,intval($item->quantity));
        }

        //Set Address to quote
        $quote->getBillingAddress()->addData($billing_address);
        $quote->getShippingAddress()->addData($shipping_address);

        // Collect Rates and Set Shipping & Payment Method
        $shipping_name = $orderData->order_adjustment->shipping->flat_rate_shipping->name;
        $carriers = $this->scopeConfig->getValue('carriers', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);


        foreach ($carriers as $carrierCode => $carrierConfig) {
            if (isset($carrierConfig['name'])) {
                if ($shipping_name == $carrierConfig['title']) {
                    $shipmethod = $this->_objectManager->get($carrierConfig['model']);
                    $allowed_methods = $shipmethod->getAllowedMethods();
                    foreach ($allowed_methods as $id => $name) {
                        //TODO we need to add support for third party shipping extensions
                        $shippingAddress = $quote->getShippingAddress();
                        $shippingAddress->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod($this->shippingmethods[$id]);
                    }
                }
            }
        }


        $msp_gateway = $orderData->payment_details->type;
        $used_method = $this->_mspHelper->getPaymentCode($msp_gateway);
        $method_activated = false;

        if ($this->_mspHelper->isMspGateway($used_method)) {
            $is_method_active = $this->getGlobalConfig('gateways/' . $used_method . '/active');
            $type = 'gateways';
        } else {
            $is_method_active = $this->getGlobalConfig('giftcards/' . $used_method . '/active');
            $type = 'giftcards';
        }

        if (!$is_method_active) {
            $this->setGlobalConfig($type . '/' . $used_method . '/active', 1);
            $method_activated = true;

            $cacheTypeList = $this->cacheTypeList;
            $cacheFrontendPool = $this->cacheFrontendPool;

            $types = ['config', 'layout', 'block_html', 'collections', 'reflection', 'db_ddl', 'eav', 'config_integration', 'config_integration_api', 'full_page', 'translate', 'config_webservice'];
            foreach ($types as $type) {
                $cacheTypeList->cleanType($type);
            }
            foreach ($cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }
        } else {
            $cacheTypeList = $this->cacheTypeList;
            $cacheFrontendPool = $this->cacheFrontendPool;

            $types = ['config', 'layout', 'block_html', 'collections', 'reflection', 'db_ddl', 'eav', 'config_integration', 'config_integration_api', 'full_page', 'translate', 'config_webservice'];
            foreach ($types as $type) {
                $cacheTypeList->cleanType($type);
            }
            foreach ($cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }
        }


        $quote->setPaymentMethod($used_method); //payment method
        //$quote->setInventoryProcessed(false); //not effect inventory
        $quote->save(); //Now Save quote and your quote is ready
        // Set Sales Order Payment
        $quote->getPayment()->importData(['method' => $used_method]);

        // Collect Totals & Save Quote
        $quote->collectTotals()->save();


        //Here we need to detect if the order has already been created, if so then we can don't need to do anything and return.
        // check if an order is already created
        $ordercollection = $this->orderCollection;
        $collection = $ordercollection->create()->addAttributeToFilter('quote_id', $quote_id);
        if (count($collection)) {
            foreach ($collection as $order) {
                return $order->getId();
            }
        }

        // Create Order From Quote
        $order = $this->quoteManagement->submit($quote);

        $order_model = $this->_order->load($order->getId());

        $this->orderNotifier->notify($order_model);
        $order->setEmailSent(1);
        $order->save();
        return $order->getId();
    }

    /*
     * This function generates a password as the generatePassword function from Magento is no longer availble for the customer object
     *
     */

    public function randomPassword($length = 8)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $length = rand(10, 16);
        $password = substr(str_shuffle(sha1(rand() . time()) . $chars), 0, $length);
        return $password;
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

    //Instructions will be visible within the order/e-mails
    public function getInstructions()
    {
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
    public function getMainConfigData($field, $storeId = null)
    {
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
    public function getConnectConfigData($field, $storeId = null)
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

    public function setGlobalConfig($path, $value)
    {
        $resourceConfig = $this->resourceConfig;

        return $resourceConfig->saveConfig($path, $value, 'default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function parseCustomerAddress($street_address)
    {
        list($address, $apartment) = $this->parseAddress($street_address);
        $customer['address'] = $address;
        $customer['housenumber'] = $apartment;
        return $customer;
    }

    public function getShippingRates($params)
    {
        //TODO we need to add support for third party shipping extensions
        $carriers = $this->scopeConfig->getValue('carriers', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        foreach ($carriers as $carrierCode => $carrierConfig) {
            if ($carrierConfig['active']) {
                if (isset($carrierConfig['price'])) {
                    $method = new \stdclass();
                    $method->id = $carrierCode;
                    $method->type = $carrierCode;
                    $method->provider = $carrierCode;
                    $method->name = $carrierConfig['title'];
                    $price = 0;
                    if ($carrierConfig['model'] == 'Magento\OfflineShipping\Model\Carrier\Flatrate') {
                        if ($carrierConfig['type'] == 'I') {
                            $price = $params['items_count'] * $carrierConfig['price'];
                        } else {
                            $price = $carrierConfig['price'];
                        }
                    } else {
                        $price = $carrierConfig['price'];
                    }
                    if (isset($carrierConfig['handling_fee'])) {
                        $method->price = $price + $carrierConfig['handling_fee'];
                    } else {
                        $method->price = $price;
                    }


                    if (!empty($carrierConfig['specificcountry'])) {
                        $areas = explode(',', $carrierConfig['specificcountry']);
                        foreach ($areas as $area) {
                            if ($area == 'NL') {//todo change me
                                $shippingMethods[] = $method;
                            }
                        }
                    } else {
                        $shippingMethods[] = $method;
                    }
                } elseif ('Magento\OfflineShipping\Model\Carrier\Freeshipping' == $carrierConfig['model']) {
                    $amount = $params['amount'];

                    if ($amount >= $carrierConfig['free_shipping_subtotal']) {
                        $method = new \stdclass();
                        $method->id = $carrierCode;
                        $method->type = $carrierCode;
                        $method->provider = $carrierCode;
                        $method->name = $carrierConfig['title'];
                        $method->price = 0;

                        if (!empty($carrierConfig['specificcountry'])) {
                            $areas = explode(',', $carrierConfig['specificcountry']);
                            foreach ($areas as $area) {
                                if ($area == 'NL') { // Todo change me
                                    $shippingMethods[] = $method;
                                }
                            }
                        } else {
                            $shippingMethods[] = $method;
                        }
                    }
                } elseif ('Magento\OfflineShipping\Model\Carrier\Tablerate' == $carrierConfig['model']) {
                    //Table rate based
                    $tablerateColl = $this->tableRateCollection;
                    foreach ($tablerateColl as $tablerate) {
                        $table_data = $tablerate->getData();
                        if ($table_data['condition_name'] == 'package_value') {
                            $items_count = $params['items_count'];
                            if ($items_count >= $table_data['condition_value']) {
                                $rate_price = $table_data['price'];
                            }
                        } elseif ($table_data['condition_name'] == 'package_weight') {
                            $item_weight = $params['weight'];
                            if ($item_weight >= $table_data['condition_value']) {
                                $rate_price = $table_data['price'];
                            }
                        }
                    }

                    $method = new \stdclass();
                    $method->id = 'tablerate';
                    $method->type = 'tablerate';
                    $method->provider = 'tablerate';
                    $method->name = $carrierConfig['title'];
                    if (isset($carrierConfig['handling_fee'])) {
                        $method->price = $rate_price + $carrierConfig['handling_fee'];
                    } else {
                        $method->price = $rate_price;
                    }

                    if (isset($carrierConfig['specificcountry'])) {
                        $ratecountries = $carrierConfig['specificcountry'];
                        $ratecountcheck = explode(',', $ratecountries);

                        if (!empty($ratecountries)) {
                            foreach ($ratecountcheck as $area) {
                                if ($area == 'NL') {//todo change me
                                    $shippingMethods[] = $method;
                                }
                            }
                        } else {
                            $shippingMethods[] = $method;
                        }
                    } else {
                        $shippingMethods[] = $method;
                    }
                }
            }
        }

        $postnl_active = $this->scopeConfig->getValue('fastcheckout/fastcheckout_postnl/postnl_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($postnl_active) {
            $method = new \stdclass();
            $method->id = 'PostNL';
            $method->type = 'pickup';
            $method->provider = 'pickup';
            $method->name = 'Post NL - Pak je gemak';
            $method->price = $this->scopeConfig->getValue('fastcheckout/fastcheckout_postnl/postnl_amount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $shippingMethods[] = $method;
        }


        foreach ($shippingMethods as $shipmethod) {
            $shipping = [];
            $shipping['id'] = $shipmethod->id;
            $shipping['name'] = $shipmethod->name;
            $shipping['cost'] = $shipmethod->price;
            $shipping['currency'] = 'EUR';
            $output[] = $shipping;
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
