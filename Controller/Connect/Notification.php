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
 * @author      Ruud Jonk <techsupport@multisafepay.com>
 * @copyright   Copyright (c) 2015 MultiSafepay, Inc. (http://www.multisafepay.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR 
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT 
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN 
 * ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION 
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace MultiSafepay\Connect\Controller\Connect;

/**
 * Responsible for loading page content.
 *
 * This is a basic controller that only loads the corresponding layout file. It may duplicate other such
 * controllers, and thus it is considered tech debt. This code duplication will be resolved in future releases.
 */
class Notification extends \Magento\Framework\App\Action\Action {

    public function execute() {
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
        //$order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($_GET['transactionid']);
        $order = $this->_objectManager->get('Magento\Sales\Model\Order');
        $order_information = $order->loadByIncrementId($_GET['transactionid']);
        
        $paymentMethod = $this->_objectManager->create('MultiSafepay\Connect\Model\Connect');
        $paymentMethod->_invoiceSender = $this->_objectManager->create('Magento\Sales\Model\Order\Email\Sender\InvoiceSender');
        $storeManager = $this->_objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        $paymentMethod->_stockInterface = $this->_objectManager->create('\Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface');
        
        $updated = $paymentMethod->notification($order);
        if ($updated) {
            if (isset($_GET['type']) && $_GET['type'] == 'initial') {
                echo '<a href="' . $storeManager->getStore()->getBaseUrl() . 'multisafepay/connect/success?transactionid=' . $_GET['transactionid'] . '"> Return back to the webshop</a>';
            } else {
                echo "ok";
            }
        } else {
            echo 'Error updating order!';
        }
    }

}
