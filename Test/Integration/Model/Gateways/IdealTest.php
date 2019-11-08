<?php

namespace MultiSafepay\Connect\Test\Integration\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
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
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function createQuote()
    {
        /** @var Address $quoteShippingAddress */
        $quoteShippingAddress = $this->objectManager->create(Address::class);

        /** @var AccountManagementInterface $accountManagement */
        $accountManagement = $this->objectManager->create(AccountManagementInterface::class);

        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $customer = $customerRepository->getById(1);

        /** @var AddressRepositoryInterface $addressRepository */
        $addressRepository = $this->objectManager->create(AddressRepositoryInterface::class);
        $quoteShippingAddress->importCustomerAddressData($addressRepository->getById(1));
        $quoteShippingAddress->setShippingMethod('flatrate_flatrate');

        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->setStoreId(1)
          ->setIsActive(true)
          ->setIsMultiShipping(false)
          ->assignCustomerWithAddressChange($customer)
          ->setShippingAddress($quoteShippingAddress)
          ->setBillingAddress($quoteShippingAddress)
          ->setCheckoutMethod('customer')
          ->setPasswordHash($accountManagement->getPasswordHash('password'))
          ->setReservedOrderId('test_order_1')
          ->setCustomerEmail('aaa@aaa.com')
          ->setQuoteCurrencyCode('EUR')
          ->setBaseGrandTotal(50);

        return $quote;
    }

    /**
     * Start testing amount restrictions
     *
     * @magentoConfigFixture default_store gateways/ideal/active 1
     * @magentoConfigFixture default_store gateways/ideal/allowed_currency EUR
     * @magentoConfigFixture default_store gateways/ideal/min_order_total 20
     * @magentoConfigFixture default_store gateways/ideal/max_order_total 100
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
     */
    public function testIsAvailableShouldReturnFalseIfOrderAmountIsNotWithinRestrictions()
    {
        $result = $this->idealInstance->isAvailable(
            $this->createQuote(
            )->setBaseGrandTotal(
                120
            )
        );
        $this->assertFalse($result);
    }

    /**
     * Start testing allowed currencies
     *
     * @magentoConfigFixture default_store gateways/ideal/active 1
     * @magentoConfigFixture default_store gateways/ideal/allowed_currency EUR
     */
    public function testIsAvailableShouldReturnTrueIfCurrencyIsAllowed()
    {
        $result = $this->idealInstance->isAvailable($this->createQuote());
        $this->assertTrue($result);
    }

    /**
     * @magentoConfigFixture default_store gateways/ideal/active 1
     * @magentoConfigFixture default_store gateways/ideal/allowed_currency USD
     */
    public function testIsAvailableShouldReturnFalseIfCurrencyIsNotAllowed()
    {
        $result = $this->idealInstance->isAvailable($this->createQuote());
        $this->assertFalse($result);
    }

    /**
     * Start testing allowed groups
     *
     * @magentoConfigFixture default_store gateways/ideal/active 1
     * @magentoConfigFixture default_store gateways/ideal/allowed_groups 1,2
     */
    public function testIsAvailableShouldReturnTrueIfGroupIsAllowed()
    {
        $result = $this->idealInstance->isAvailable($this->createQuote());
        $this->assertTrue($result);
    }

    /**
     * @magentoConfigFixture default_store gateways/ideal/active 1
     * @magentoConfigFixture default_store gateways/ideal/allowed_groups 0,2
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
     */
    public function testIsAvailableShouldReturnTrueIfShippingMethodIsAllowed()
    {
        $result = $this->idealInstance->isAvailable($this->createQuote());
        $this->assertTrue($result);
    }

    /**
     * @magentoConfigFixture default_store gateways/ideal/active 1
     * @magentoConfigFixture default_store gateways/ideal/allowed_carrier_active 1
     * @magentoConfigFixture default_store gateways/ideal/allowed_carrier tablerate_bestway
     */
    public function testIsAvailableShouldReturnFalseIfShippingMethodIsNotAllowed()
    {
        $result = $this->idealInstance->isAvailable($this->createQuote());
        $this->assertFalse($result);
    }
}
