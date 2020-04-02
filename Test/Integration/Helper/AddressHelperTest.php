<?php

namespace MultiSafepay\Connect\Test\Integration\Helper;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\Order\Address;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use MultiSafepay\Connect\Helper\AddressHelper;
use PHPUnit\Framework\TestCase;

class AddressHelperTest extends TestCase
{
    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * @var AddressHelper
     */
    private $addressHelperInstance;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
        $this->addressHelperInstance = $this->objectManager->get(AddressHelper::class);
    }

    /**
     * @return Address
     */
    private function getShippingAddress(): Address
    {
        /** @var $address Address */
        $address = Bootstrap::getObjectManager()->create(Address::class);

        $address->setRegion('NL')
            ->setPostcode('1033SC')
            ->setFirstname('MultiSafepayFirstName')
            ->setLastname('MultiSafepayLastName')
            ->setStreet('Kraanspoor 39')
            ->setCity('Amsterdam')
            ->setEmail('test@example.com')
            ->setTelephone('0208500500')
            ->setCountryId('NL')
            ->setAddressType('shipping')
            ->save();

        return $address;
    }

    /**
     * @return Address
     */
    private function getBillingAddress(): Address
    {
        /** @var $address Address */
        $address = Bootstrap::getObjectManager()->create(Address::class);

        $address->setRegion('CA')
            ->setPostcode('90210')
            ->setFirstname('TestFirstName')
            ->setLastname('TestLastName')
            ->setStreet('teststreet 50')
            ->setCity('Beverly Hills')
            ->setEmail('admin@example.com')
            ->setTelephone('1111111111')
            ->setCountryId('US')
            ->setAddressType('billing')
            ->save();

        return $address;
    }

    /**
     * @param $string
     * @return bool
     */
    private function isSerialized($string): bool
    {
        $serializer = Bootstrap::getObjectManager()->create(SerializerInterface::class);

        return $serializer->unserialize($string) !== false;
    }

    public function testSerializeAddressShouldReturnSerializedAddress()
    {
        $address = $this->getShippingAddress();

        $result = $this->addressHelperInstance->serializeAddress($address);

        $this->assertInternalType('string', $result);
        $this->assertTrue($this->isSerialized($result));
    }

    public function testIsSameAddressShouldReturnFalseIfDifferentBillingAndShipping()
    {
        $shippingAddress = $this->getShippingAddress();
        $billingAddress = $this->getBillingAddress();

        $result = $this->addressHelperInstance->isSameAddress($shippingAddress, $billingAddress);

        $this->assertFalse($result);
    }

    public function testIsSameAddressShouldReturnTrueIfSameBillingAndShipping()
    {
        $shippingAddress = $this->getShippingAddress();
        $billingAddress = $this->getBillingAddress()
            ->setStreet('Kraanspoor 39')
            ->setPostcode('1033SC')
            ->setCity('Amsterdam');

        $result = $this->addressHelperInstance->isSameAddress($shippingAddress, $billingAddress);

        $this->assertTrue($result);
    }
}
