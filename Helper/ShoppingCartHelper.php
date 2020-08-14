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

declare(strict_types=1);

namespace MultiSafepay\Connect\Helper;


use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Model\Product\Type;
use Magento\Sales\Model\Order\Item;

class ShoppingCartHelper
{
    /**
     * @param Item $item
     * @return bool
     */
    public function canAddtoShoppingCart(Item $item): bool
    {
        $productType = $item->getProductType();
        $product = $item->getProduct();
        $parentItem = $item->getParentItem();

        if ($product !== null) {

            // Bundled products with price type dynamic should not be added, we want the simple products instead
            if ($productType === Type::TYPE_BUNDLE
                && (int) $product->getPriceType() === Price::PRICE_TYPE_DYNAMIC) {
                return false;
            }

            // Products with no parent can be added
            if ($parentItem === null) {
                return true;
            }

            $parentItemProductType = $parentItem->getProductType();

            // We do not want to add the item if the parent item is not a bundle
            if ($parentItemProductType !== Type::TYPE_BUNDLE) {
                return false;
            }

            // Do not add the item if the parent is a fixed price bundle product, the bundle product is added instead
            if ($parentItemProductType === Type::TYPE_BUNDLE
                && ($parentItem->getProduct() !== null)
                && (int) $parentItem->getProduct()->getPriceType() === Price::PRICE_TYPE_FIXED) {
                return false;
            }
        }

        return true;
    }
}
