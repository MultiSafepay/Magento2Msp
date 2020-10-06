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
                'paymentMethods' => [$this->getVisaObject()],
                'expected' => "<b>Payment methods specification:</b><br />VISA (€10.00)",
            ],
            [
                'paymentMethods' => [
                    $this->getVisaObject(),
                    $this->getCouponObject()
                ],
                'expected' => "<b>Payment methods specification:</b><br />VISA (€10.00) / COUPON (€10.00)",
            ],
            [
                'paymentMethods' => [
                    $this->getVisaObject(),
                    $this->getCouponObject(),
                    $this->getCouponObject()
                ],
                'expected' => "<b>Payment methods specification:</b><br />VISA (€10.00) / COUPON (€10.00) / COUPON (€10.00)",
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

    /**
     * @dataProvider getGatewayProvider
     * @dataProvider getGiftcardProvider
     * @param $gatewayCode
     */
    public function testGetPaymentType($gatewayCode)
    {
        $result = $this->helper->getPaymentType($gatewayCode);

        $this->assertEquals($this->helper->gateways[$gatewayCode]['type'], $result);
    }

    /**
     * @dataProvider getGatewayProvider
     * @param $gateway
     */
    public function testIsMspGateway($gateway)
    {
        $result = $this->helper->isMspGateway($gateway);

        $this->assertTrue($result);
    }

    /**
     * @dataProvider getGiftcardProvider
     * @param $giftcard
     */
    public function testIsMspGiftcard($giftcard)
    {
        $result = $this->helper->isMspGiftcard($giftcard);

        $this->assertTrue($result);
    }

    public function getGatewayProvider()
    {
        return [
            ['afterpaymsp'],
            ['alipay'],
            ['americanexpress'],
            ['bancontact'],
            ['belfius'],
            ['betaalnaontvangst'],
            ['betaalplan'],
            ['creditcard'],
            ['directbanktransfer'],
            ['directdebit'],
            ['dotpay'],
            ['einvoice'],
            ['eps'],
            ['giropay'],
            ['ideal'],
            ['idealqr'],
            ['in3'],
            ['ing'],
            ['cbc'],
            ['kbc'],
            ['klarnainvoice'],
            ['maestro'],
            ['mastercard'],
            ['mspbanktransfer'],
            ['multisafepay'],
            ['paypalmsp'],
            ['paysafecard'],
            ['sofort'],
            ['trustly'],
            ['trustpay'],
            ['visa'],
        ];
    }

    public function getGiftcardProvider()
    {
        return [
            ['babygiftcard'],
            ['beautyandwellness'],
            ['boekenbon'],
            ['erotiekbon'],
            ['fashioncheque'],
            ['fashiongiftcard'],
            ['fietsenbon'],
            ['gezondheidsbon'],
            ['givacard'],
            ['goodcard'],
            ['nationaletuinbon'],
            ['nationaleverwencadeaubon'],
            ['parfumcadeaukaart'],
            ['podiumcadeaukaart'],
            ['sportenfit'],
            ['vvvbon'],
            ['webshopgiftcard'],
            ['wellnessgiftcard'],
            ['wijncadeau'],
            ['winkelcheque'],
            ['yourgift'],
        ];
    }


    public function testGetAllMethods()
    {
        $result = $this->helper->getAllMethods();

        $methods = [
            'afterpaymsp' => 'AfterPay',
            'alipay' => 'Alipay',
            'americanexpress' => 'American Express',
            'applepay' => 'Apple Pay',
            'bancontact' => 'Bancontact',
            'belfius' => 'Belfius',
            'betaalnaontvangst' => 'Pay After Delivery',
            'betaalplan' => 'Betaalplan',
            'creditcard' => 'Credit card',
            'directdebit' => 'Direct Debit',
            'directbanktransfer' => 'Request to Pay',
            'dotpay' => 'Dotpay',
            'einvoice' => 'E-Invoicing',
            'eps' => 'EPS',
            'giropay' => 'GiroPay',
            'ideal' => 'iDEAL',
            'idealqr' => 'iDEAL QR',
            'in3' => 'in3',
            'ing' => 'ING Home\'Pay',
            'cbc' => 'CBC',
            'kbc' => 'KBC',
            'klarnainvoice' => 'Klarna - buy now, pay later',
            'maestro' => 'Maestro',
            'mastercard' => 'Mastercard',
            'mspbanktransfer' => 'Bank transfer',
            'multisafepay' => 'MultiSafepay',
            'paypalmsp' => 'PayPal',
            'paysafecard' => 'Paysafecard',
            'sofort' => 'SOFORT Banking',
            'trustly' => 'Trustly',
            'trustpay' => 'Trustpay',
            'visa' => 'Visa',
            'babygiftcard' => 'Babygiftcard',
            'beautyandwellness' => 'Beauty and wellness',
            'boekenbon' => 'Boekenbon',
            'erotiekbon' => 'Erotiekbon',
            'fashioncheque' => 'Fashioncheque',
            'fashiongiftcard' => 'Fashiongiftcard',
            'fietsenbon' => 'Fietsenbon',
            'gezondheidsbon' => 'Gezondheidsbon',
            'givacard' => 'Givacard',
            'goodcard' => 'Goodcard',
            'nationaletuinbon' => 'Nationale tuinbon',
            'nationaleverwencadeaubon' => 'Nationale verwencadeaubon',
            'parfumcadeaukaart' => 'Parfumcadeaukaart',
            'podiumcadeaukaart' => 'Podium',
            'sportenfit' => 'Sportenfit',
            'vvvbon' => 'VVV Cadeaukaart',
            'webshopgiftcard' => 'Webshop Giftcard',
            'wellnessgiftcard' => 'Wellness Giftcards',
            'wijncadeau' => 'Wijn Cadeau',
            'winkelcheque' => 'Winkel Cheque',
            'yourgift' => 'YourGift',
        ];

        $this->assertEquals($methods, $result);
    }
}
