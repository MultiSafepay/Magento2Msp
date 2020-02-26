<?php

namespace MultiSafepay\Connect\Test\Integration\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\TestFramework\ObjectManager;
use MultiSafepay\Connect\Model\Gateways\Ideal;
use PHPUnit\Framework\TestCase;

class IdealTest extends TestCase
{
    protected $idealInstance;
    protected $objectManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
        $this->idealInstance = $this->objectManager->get(Ideal::class);
    }

    /**
     * @return Quote
     */
    protected function createQuote(): Quote
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->setStoreId(1)
            ->setIsActive(true)
            ->setIsMultiShipping(false)
            ->setCheckoutMethod('customer')
            ->setReservedOrderId('test_order_1')
            ->setCustomerEmail('aaa@aaa.com')
            ->setQuoteCurrencyCode('EUR')
            ->setBaseGrandTotal(50);

        return $quote;
    }

    /**
     * @return Address
     * @throws LocalizedException
     */
    protected function createShippingAddress(): Address
    {
        /** @var Address $quoteShippingAddress */
        $quoteShippingAddress = $this->objectManager->create(Address::class);

        /** @var AddressRepositoryInterface $addressRepository */
        $addressRepository = $this->objectManager->create(AddressRepositoryInterface::class);
        $quoteShippingAddress->importCustomerAddressData($addressRepository->getById(1));
        $quoteShippingAddress->setShippingMethod('flatrate_flatrate');

        return $quoteShippingAddress;
    }

    /**
     * Start testing amount restrictions
     *
     * @magentoConfigFixture default_store gateways/ideal/active 1
     * @magentoConfigFixture default_store gateways/ideal/allowed_currency EUR
     * @magentoConfigFixture default_store gateways/ideal/min_order_total 20
     * @magentoConfigFixture default_store gateways/ideal/max_order_total 100
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address.php
     */
    public function testIsAvailableShouldReturnTrueIfOrderAmountIsWithinRestrictions()
    {
        $result = $this->idealInstance->isAvailable($this->createQuote());

        $this->assertTrue($result);
    }

    /**
     * @magentoConfigFixture default_store gateways/ideal/active 1
     * @magentoConfigFixture default_store gateways/ideal/allowed_currency EUR
     * @magentoConfigFixture default_store gateways/ideal/min_order_total 20
     * @magentoConfigFixture default_store gateways/ideal/max_order_total 100
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address.php
     */
    public function testIsAvailableShouldReturnFalseIfOrderAmountIsNotWithinRestrictions()
    {
        $result = $this->idealInstance->isAvailable($this->createQuote()->setBaseGrandTotal(120));
        $this->assertFalse($result);
    }

    /**
     * Start testing allowed currencies
     *
     * @magentoConfigFixture default_store gateways/ideal/active 1
     * @magentoConfigFixture default_store gateways/ideal/allowed_currency EUR
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address.php
     */
    public function testIsAvailableShouldReturnTrueIfCurrencyIsAllowed()
    {
        $result = $this->idealInstance->isAvailable($this->createQuote());
        $this->assertTrue($result);
    }

    /**
     * @magentoConfigFixture default_store gateways/ideal/active 1
     * @magentoConfigFixture default_store gateways/ideal/allowed_currency USD
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address.php
     */
    public function testIsAvailableShouldReturnFalseIfCurrencyIsNotAllowed()
    {
        $result = $this->idealInstance->isAvailable($this->createQuote());
        $this->assertFalse($result);
    }

    /**
     * @magentoConfigFixture default_store gateways/ideal/active 1
     * @magentoConfigFixture default_store gateways/ideal/allowed_groups 0,2
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address.php
     */
    public function testIsAvailableShouldReturnTrueIfGroupIsAllowed()
    {
        $result = $this->idealInstance->isAvailable($this->createQuote());

        $this->assertTrue($result);
    }

    /**
     * Start testing allowed groups
     *
     * @magentoConfigFixture default_store gateways/ideal/active 1
     * @magentoConfigFixture default_store gateways/ideal/allowed_groups 1,2
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address.php
     */
    public function testIsAvailableShouldReturnFalseIfGroupIsNotAllowed()
    {
        $result = $this->idealInstance->isAvailable($this->createQuote());

        $this->assertFalse($result);
    }

    /**
     * Start testing allowed shipping methods
     *
     * @magentoConfigFixture default_store gateways/ideal/active 1
     * @magentoConfigFixture default_store gateways/ideal/allowed_carrier_active 0
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address.php
     */
    public function testIsAvailableShouldReturnTrueIfShippingMethodRestrictionIsDisabled()
    {
        $result = $this->idealInstance->isAvailable($this->createQuote());
        $this->assertTrue($result);
    }

    /**
     * @magentoConfigFixture default_store gateways/ideal/active 1
     * @magentoConfigFixture default_store gateways/ideal/allowed_carrier_active 1
     * @magentoConfigFixture default_store gateways/ideal/allowed_carrier flatrate_flatrate
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address.php
     * @throws LocalizedException
     */
    public function testIsAvailableShouldReturnTrueIfShippingMethodIsAllowed()
    {
        $result = $this->idealInstance->isAvailable(
            $this->createQuote()->setShippingAddress($this->createShippingAddress())
        );
        $this->assertTrue($result);
    }

    /**
     * @magentoConfigFixture default_store gateways/ideal/active 1
     * @magentoConfigFixture default_store gateways/ideal/allowed_carrier_active 1
     * @magentoConfigFixture default_store gateways/ideal/allowed_carrier tablerate_bestway
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address.php
     * @throws LocalizedException
     */
    public function testIsAvailableShouldReturnFalseIfShippingMethodIsNotAllowed()
    {
        $result = $this->idealInstance->isAvailable(
            $this->createQuote()->setShippingAddress($this->createShippingAddress())
        );
        $this->assertFalse($result);
    }
}
