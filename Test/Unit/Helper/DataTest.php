<?php
namespace MultiSafepay\Connect\Test\Unit\Helper;

use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection as orderStatusCollection;
use Magento\Store\Model\StoreManagerInterface;
use MultiSafepay\Connect\Helper\Data;
use MultiSafepay\Connect\Model\MultisafepayTokenizationFactory;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Data
     */
    private $helper;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->helper = $objectManager->getObject(Data::class);
    }

    public function testDataInstance()
    {
        $this->assertInstanceOf(Data::class, $this->helper);
    }

    public function testMultiPaymentMethodsLineCreationWithNoPaymentMethods()
    {
        $result = $this->helper->createMultiPaymentMethodsLine([]);
        $this->assertEquals('', $result);
    }

    /**
     * @dataProvider paymentMethodsProvider
     */
    public function testMultiPaymentMethodsLineCreationShouldCreateMultipleEntry($paymentMethods, $expected)
    {
        $objectManager = new ObjectManager($this);

        $storeManagerInterface = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderStatusCollection = $this->getMockBuilder(orderStatusCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfigInterface = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $random = $this->getMockBuilder(Random::class)
            ->disableOriginalConstructor()
            ->getMock();
        $encryptor = $this->getMockBuilder(EncryptorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $multisafepayTokenizationFactory = $this->getMockBuilder(MultisafepayTokenizationFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $currencyFactory = $this->getMockBuilder('Magento\Directory\Model\CurrencyFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $currency = $this->getMockBuilder('Magento\Directory\Model\Currency')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'formatTxt'])
            ->getMock();

        $currency->expects($this->atLeastOnce())
            ->method('load')
            ->with('EUR')
            ->will($this->returnSelf());

        $currency->expects($this->atLeastOnce())
            ->method('formattxt')
            ->with(10)
            ->willReturn('€10.00');
        $currencyFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($currency);


        $this->helper = $objectManager->getObject(
            Data::class,
            [
                'StoreManagerInterface' => $storeManagerInterface,
                'orderStatusCollection' => $orderStatusCollection,
                'Filesystem' => $filesystem,
                'ScopeConfigInterface' => $scopeConfigInterface,
                'Random' => $random,
                'MultisafepayTokenizationFactory' => $multisafepayTokenizationFactory,
                'EncryptorInterface' => $encryptor,
                'currencyFactory' => $currencyFactory,
            ]
        );

        $result = $this->helper->createMultiPaymentMethodsLine($paymentMethods);
        $this->assertEquals($expected, $result);
    }

    public function paymentMethodsProvider()
    {
        return [
            [
                'paymentMethods'  => [$this->getVisaObject()],
                'expected'  => "<b>Payment methods specification:</b><br />VISA (€10.00)",
            ],
            [
                'paymentMethods'  => [
                    $this->getVisaObject(),
                    $this->getCouponObject()
                ],
                'expected'  => "<b>Payment methods specification:</b><br />VISA (€10.00) / COUPON (€10.00)",
            ],
            [
                'paymentMethods'  => [
                    $this->getVisaObject(),
                    $this->getCouponObject(),
                    $this->getCouponObject()
                ],
                'expected'  => "<b>Payment methods specification:</b><br />VISA (€10.00) / COUPON (€10.00) / COUPON (€10.00)",
            ],
        ];
    }

    private function getVisaObject()
    {
        $visa = new \stdClass();
        $visa->currency = 'EUR';
        $visa->amount = 1000;
        $visa->type = 'VISA';
        return $visa;
    }


    private function getCouponObject()
    {
        $coupon = new \stdClass();
        $coupon->currency = 'EUR';
        $coupon->amount = 1000;
        $coupon->type = 'COUPON';
        return $coupon;
    }
}