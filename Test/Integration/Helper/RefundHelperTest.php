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
 * @copyright   Copyright (c) 2019 MultiSafepay, Inc. (https://www.multisafepay.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
 * ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace MultiSafepay\Connect\Test\Integration\Helper;

use MultiSafepay\Connect\Helper\RefundHelper;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\Sales\Model\Order;

class RefundHelperTest extends TestCase
{
    /**
     * @var RefundHelper
     */
    protected $refundHelperInstance;
    protected $objectManager;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
        $this->refundHelperInstance = $this->objectManager->get(RefundHelper::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testCalculateAdjustmentsWithIntegerAsString()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(Order::class)->loadByIncrementId('100000001');
        $order->setBaseToOrderRate(1);

        $expected = 20;
        $result = $this->refundHelperInstance->calculateAdjustments('20', $order);

        $this->assertEquals($expected, $result);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testCalculateAdjustmentsWithPercentage()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(Order::class)->loadByIncrementId('100000001');
        $order->setBaseToOrderRate(1);

        $expected = 20;
        $result = $this->refundHelperInstance->calculateAdjustments('20%', $order);

        $this->assertEquals($expected, $result);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testGetAdjustmentOrderLinesWithNoAdjustments()
    {
        $refundData = $this->getRefundData();
        $order = $this->objectManager->create(Order::class)->loadByIncrementId('100000001');
        $order->setBaseToOrderRate(1);

        $result = $this->refundHelperInstance->getAdjustmentOrderLines($refundData, $order);

        $this->assertEmpty($result);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testGetAdjustmentOrderLinesWithPositiveAdjustments()
    {
        $refundData = $this->getRefundData();
        $refundData['adjustment_positive'] = '20%';

        $order = $this->objectManager->create(Order::class)->loadByIncrementId('100000001');
        $order->setBaseToOrderRate(1);

        $result = $this->refundHelperInstance->getAdjustmentOrderLines($refundData, $order);

        $this->assertCount(1, $result);
        $this->assertEquals('adjustment_positive', $result[0]->name);
        $this->assertEquals(20, $result[0]->unit_price);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testGetAdjustmentOrderLinesWithNegativeAdjustments()
    {
        $refundData = $this->getRefundData();
        $refundData['adjustment_negative'] = '20';

        $order = $this->objectManager->create(Order::class)->loadByIncrementId('100000001');
        $order->setBaseToOrderRate(1);

        $result = $this->refundHelperInstance->getAdjustmentOrderLines($refundData, $order);

        $this->assertCount(1, $result);
        $this->assertEquals('adjustment_negative', $result[0]->name);
        $this->assertEquals(-20, $result[0]->unit_price);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testGetAdjustmentOrderLinesWithBothAdjustments()
    {
        $refundData = $this->getRefundData();
        $refundData['adjustment_negative'] = '20';
        $refundData['adjustment_positive'] = '20%';

        $order = $this->objectManager->create(Order::class)->loadByIncrementId('100000001');
        $order->setBaseToOrderRate(1);

        $result = $this->refundHelperInstance->getAdjustmentOrderLines($refundData, $order);

        $this->assertCount(2, $result);

        foreach ($result as $refundLine) {
            if ($refundLine->name === 'adjustment_negative') {
                $this->assertEquals('adjustment_negative', $refundLine->name);
                $this->assertEquals(-20, $refundLine->unit_price);
            } else {
                $this->assertEquals('adjustment_positive', $refundLine->name);
                $this->assertEquals(20, $refundLine->unit_price);
            }
        }
    }

    /**
     * @return array
     */
    private function getRefundData()
    {
        return [
            'items' => [8 => '1'],
            'do_offline' => '0',
            'comment_text' => '',
            'shipping_amount' => '15',
            'adjustment_positive' => '0',
            'adjustment_negative' => '0'
        ];
    }
}
