<?php

/**
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

namespace MultiSafepay\Connect\Helper;

use Magento\Sales\Model\Order\Creditmemo;

/**
 * Class RefundHelper
 */
class RefundHelper
{
    /**
     * Create a default refund item rule for MultiSafepay refund Api request.
     *
     * @param string $name
     * @param int|float $price
     * @param int $quantity
     * @param string $merchantItemId
     * @param string $taxTableSelector
     * @param string $description
     * @return \stdClass
     */
    public function createRefundItem($name, $price, $quantity, $merchantItemId, $taxTableSelector, $description = null)
    {
        $refundItem = new \stdClass();
        $refundItem->name = $name;
        $refundItem->description = $description === null ? $name : $description;
        $refundItem->unit_price = $price;
        $refundItem->quantity = $quantity;
        $refundItem->merchant_item_id = $merchantItemId;
        $refundItem->tax_table_selector = $taxTableSelector;

        return $refundItem;
    }


    /**
     * Create the order lines for adjustments calculations.
     *
     * @param Creditmemo $creditmemo
     * @return array
     */
    public function getAdjustmentOrderLines(Creditmemo $creditmemo)
    {
        $mspRefundLines = [];

        // Adjustment Refund
        $adjustmentPositive = $creditmemo->getAdjustmentPositive();
        if ($adjustmentPositive > 0) {
            $mspRefundLines[] = $this->createRefundItem(
                'adjustmentPositive',
                $adjustmentPositive * $creditmemo->getBaseToOrderRate(),
                1,
                'adjustmentPositive',
                'adjustment'
            );
        }

        // Adjustment Fee
        $adjustmentNegative = $creditmemo->getAdjustmentNegative();
        if ($adjustmentNegative > 0) {
            $mspRefundLines[] = $this->createRefundItem(
                'adjustmentNegative',
                0 - ($adjustmentNegative *  $creditmemo->getBaseToOrderRate()),
                1,
                'adjustmentNegative',
                'adjustment'
            );
        }

        return $mspRefundLines;
    }
}
