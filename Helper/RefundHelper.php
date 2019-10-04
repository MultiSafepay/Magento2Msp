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

use Magento\Sales\Model\Order;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class RefundHelper
 */
class RefundHelper
{
    protected $priceCurrency;

    /**
     * RefundHelper constructor.
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(PriceCurrencyInterface $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }


    /**
     * Calculate the adjustments amount.
     *
     * @param string|int $amount
     * @param Order $order
     * @return float|int|string
     */
    public function calculateAdjustments($amount, Order $order)
    {
        $amount = trim($amount);

        if (substr($amount, -1) == '%') {
            $amount = (double)substr($amount, 0, -1);
            $amount = $order->getGrandTotal() * $amount / 100;
        }

        $amount = $this->priceCurrency->round($amount);
        $amount = $this->priceCurrency->round($amount * $order->getBaseToOrderRate());

        return $amount;
    }


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
     * @param array $refundData
     * @param Order $order
     * @return array
     */
    public function getAdjustmentOrderLines($refundData, Order $order)
    {
        $mspRefundLines = [];

        foreach ($refundData as $key => $refundLine) {
            if (in_array($key, ['adjustment_positive', 'adjustment_negative']) && $refundLine > 0) {
                $adjustmentAmount = $this->calculateAdjustments($refundLine, $order);

                $mspRefundLines[] = $this->createRefundItem(
                    $key,
                    ($key === 'adjustment_negative') ? 0 - $adjustmentAmount : $adjustmentAmount,
                    1,
                    'adjustment',
                    'adjustment'
                );
            }
        }

        return $mspRefundLines;
    }
}
