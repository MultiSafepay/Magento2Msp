<?php

namespace MultiSafepay\Connect\Test\Integration\Model;

use Magento\TestFramework\ObjectManager;
use Magento\Sales\Model\Order;
use MultiSafepay\Connect\Model\Connect;
use PHPUnit\Framework\TestCase;

class ConnectTest extends TestCase
{
    /**
     * @var Connect
     */
    protected $connectInstance;
    protected $objectManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
        $this->connectInstance = $this->objectManager->get(Connect::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testGetCheckoutDataShouldReturnPriceAsUnitPriceWhenBaseCurrencyIsFalse()
    {
        /** @var Order $order */
        $order = $this->objectManager->create(Order::class)->loadByIncrementId('100000001');

        $result = $this->connectInstance->getCheckoutData($order, false);

        $unitPrice = $result['shopping_cart']['items'][0]['unit_price'];

        foreach ($order->getAllItems() as $item) {
            $productPrice = $item->getPrice();

            $this->assertEquals($unitPrice, $productPrice);
        }
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testGetCheckoutDataShouldReturnBasePriceAsUnitPriceWhenBaseCurrencyIsTrue()
    {
        /** @var Order $order */
        $order = $this->objectManager->create(Order::class)->loadByIncrementId('100000001');

        $result = $this->connectInstance->getCheckoutData($order, true);

        $unitPrice = $result['shopping_cart']['items'][0]['unit_price'];

        foreach ($order->getAllItems() as $item) {
            $productPrice = $item->getBasePrice();

            $this->assertEquals($unitPrice, $productPrice);
        }
    }

    public function testIsAvailableShouldReturnFalseIfCodeIsConnect()
    {
        $result = $this->connectInstance->isAvailable();
        $this->assertFalse($result);
    }

    public function testSaveCreditcardTokenWhileNotUsingCreditCardGateway()
    {
        $this->assertFalse($this->connectInstance->canSaveCreditCardToken([]));
    }
}
