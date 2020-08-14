<?php


namespace MultiSafepay\Connect\Test\Integration\Model\Gateways;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Model\Session;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\StatusResolver;
use Magento\Sales\Model\OrderNotifier;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\ObjectManager;
use MultiSafepay\Connect\Helper\AddressHelper;
use MultiSafepay\Connect\Helper\Data as HelperData;
use MultiSafepay\Connect\Helper\RefundHelper;
use MultiSafepay\Connect\Helper\ShoppingCartHelper;
use MultiSafepay\Connect\Helper\UndoCancel;
use MultiSafepay\Connect\Model\Api\MspClient;
use MultiSafepay\Connect\Model\Config\Source\Creditcards;
use MultiSafepay\Connect\Model\GatewayRestrictions;
use MultiSafepay\Connect\Model\Gateways\Betaalnaontvangst;
use MultiSafepay\Connect\Model\MultisafepayTokenizationFactory;
use MultiSafepay\Connect\Model\Url;
use PHPUnit\Framework\TestCase;

class BetaalnaontvangstTest extends TestCase
{
    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * @var Betaalnaontvangst
     */
    private $payAfterInstance;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();

        $checkoutSession = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();
        $checkoutSession->method('getQuote')->willReturn($this->createQuote());

        $this->payAfterInstance = $this->getMockBuilder(Betaalnaontvangst::class)->setConstructorArgs(
            [
                $this->objectManager->create(Context::class),
                $this->objectManager->create(Registry::class),
                $this->objectManager->create(ExtensionAttributesFactory::class),
                $this->objectManager->create(AttributeValueFactory::class),
                $this->objectManager->create(Data::class),
                $this->objectManager->create(ScopeConfigInterface::class),
                $this->objectManager->create(Logger::class),
                $this->objectManager->create(ModuleListInterface::class),
                $this->objectManager->create(TimezoneInterface::class),
                $this->objectManager->create(StoreManagerInterface::class),
                $checkoutSession,
                $this->objectManager->create(UrlInterface::class),
                $this->objectManager->create(RequestInterface::class),
                $this->objectManager->create(StockRegistryInterface::class),
                $this->objectManager->create(InvoiceSender::class),
                $this->objectManager->create(ProductMetadataInterface::class),
                $this->objectManager->create(InvoiceRepositoryInterface::class),
                $this->objectManager->create(TransactionRepositoryInterface::class),
                $this->objectManager->create(Resolver::class),
                $this->objectManager->create(OrderRepositoryInterface::class),
                $this->objectManager->create(OrderNotifier::class),
                $this->objectManager->create(StatusResolver::class),
                $this->objectManager->create(CurrencyFactory::class),
                $this->objectManager->create(MultisafepayTokenizationFactory::class),
                $this->objectManager->create(MspClient::class),
                $this->objectManager->create(HelperData::class),
                $this->objectManager->create(Url::class),
                $this->objectManager->create(Creditcards::class),
                $this->objectManager->create(\Magento\Customer\Model\Session::class),
                $this->objectManager->create(RefundHelper::class),
                $this->objectManager->create(AddressHelper::class),
                $this->objectManager->create(UndoCancel::class),
                $this->objectManager->create(GatewayRestrictions::class),
                $this->objectManager->create(ShoppingCartHelper::class),
                $this->objectManager->create(DataObjectFactory::class),
                $this->getMockBuilder(AbstractResource::class)->disableOriginalConstructor()->getMock(),
                $this->getMockBuilder(AbstractDb::class)->disableOriginalConstructor()->getMock(),
            ]
        )->setMethodsExcept(['validate'])->getMock();
    }

    /**
     * @return Quote
     */
    protected function createQuote(): Quote
    {
        /** @var Address $quoteShippingAddress */
        $quoteShippingAddress = $this->objectManager->create(Address::class);
        $quoteShippingAddress->setStreet('Kraanspoor 39')
            ->setPostcode('1033SC')
            ->setCity('Amsterdam');

        /** @var Address $quoteBillingAddress */
        $quoteBillingAddress = $this->objectManager->create(Address::class);
        $quoteBillingAddress->setStreet('teststreet 50')
            ->setPostcode('90210')
            ->setCity('Beverly Hills');

        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->setStoreId(1)
            ->setIsActive(true)
            ->setIsMultiShipping(false)
            ->setShippingAddress($quoteShippingAddress)
            ->setBillingAddress($quoteBillingAddress)
            ->setCheckoutMethod('customer')
            ->setReservedOrderId('test_order_1')
            ->setCustomerEmail('test@example.com')
            ->setQuoteCurrencyCode('EUR');

        return $quote;
    }

    /**
     * @throws LocalizedException
     */
    public function testValidateShouldThrowErrorIfDifferentShippingAndBilling()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('This gateway does not allow a different billing and shipping address');
        $this->payAfterInstance->validate();
    }
}
