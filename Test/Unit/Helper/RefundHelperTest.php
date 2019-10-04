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

namespace MultiSafepay\Connect\Test\Unit\Helper;

use MultiSafepay\Connect\Helper\RefundHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class RefundHelperTest extends TestCase
{
    protected $objectManager;

    /**
     * @var RefundHelper
     */
    protected $refundHelper;


    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = new ObjectManager($this);
        $this->refundHelper = $this->objectManager->getObject(RefundHelper::class);
    }

    /**
     * Test function CreateRefundItem with and description so it will fall back on the default
     */
    public function testCreateRefundItemWithDescription()
    {
        $result = $this->refundHelper->createRefundItem(
            'Dummy-Name',
            1,
            2,
            'Dummy-merchant_item_id',
            'Dummy-tax_table_selector',
            'A Sample Description'
        );
        $expected = 'A Sample Description';

        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertEquals('Dummy-Name', $result->name);
        $this->assertEquals($expected, $result->description);
        $this->assertEquals(1, $result->unit_price);
        $this->assertEquals(2, $result->quantity);
        $this->assertEquals('Dummy-merchant_item_id', $result->merchant_item_id);
        $this->assertEquals('Dummy-tax_table_selector', $result->tax_table_selector);
    }

    /**
     * Test function CreateRefundItem Without and description so it will fall back on the default
     */
    public function testCreateRefundItemWithoutDescription()
    {
        $result = $this->refundHelper->createRefundItem(
            'Dummy-Name',
            1,
            2,
            'Dummy-merchant_item_id',
            'Dummy-tax_table_selector'
        );
        $expected = 'Dummy-Name';

        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertEquals('Dummy-Name', $result->name);
        $this->assertEquals($expected, $result->description);
        $this->assertEquals(1, $result->unit_price);
        $this->assertEquals(2, $result->quantity);
        $this->assertEquals('Dummy-merchant_item_id', $result->merchant_item_id);
        $this->assertEquals('Dummy-tax_table_selector', $result->tax_table_selector);
    }
}
