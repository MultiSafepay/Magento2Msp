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

use MultiSafepay\Connect\Helper\Data;

/**
 * Responsible for loading page content.
 *
 * This is a basic controller that only loads the corresponding layout file. It may duplicate other such
 * controllers, and thus it is considered tech debt. This code duplication will be resolved in future releases.
 */
class Notification extends \Magento\Framework\App\Action\Action
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    protected $_mspHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_requestHttp;

    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Framework\Registry $coreRegistry
    )
    {
        $this->_coreRegistry = $coreRegistry;
        $this->_requestHttp = $context->getRequest();
        parent::__construct($context);
        $this->_mspHelper = new \MultiSafepay\Connect\Helper\Data;
    }

    public function execute()
    {
        $params = $this->_requestHttp->getParams();
        $this->_mspHelper->lockProcess('multisafepay-' . $params['transactionid']);
        if (!isset($params['timestamp'])) {
            $this->getResponse()->setContent('No timestamp is set so we are stopping the callback');
            $this->_mspHelper->unlockProcess('multisafepay-' . $params['transactionid']);
            return false;
        }
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
        $order = $this->_objectManager->get('Magento\Sales\Model\Order');
        $order_information = $order->loadByIncrementId($params['transactionid']);

        $paymentMethod = $this->_objectManager->create('MultiSafepay\Connect\Model\Connect');
        $paymentMethod->_invoiceSender = $this->_objectManager->create('Magento\Sales\Model\Order\Email\Sender\InvoiceSender');
        $storeManager = $this->_objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        $paymentMethod->_stockInterface = $this->_objectManager->create('\Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface');

        $updated = $paymentMethod->notification($order);
        $this->_mspHelper->unlockProcess('multisafepay-' . $params['transactionid']);
        if ($updated) {
            if (isset($params['type']) && $params['type'] == 'initial') {
                $this->getResponse()->setContent('<a href="' . $storeManager->getStore()->getBaseUrl() . 'multisafepay/connect/success?transactionid=' . $params['transactionid'] . '"> Return back to the webshop</a>');
            } else {
                $this->getResponse()->setContent('ok');
            }
        } else {
            $this->getResponse()->setContent('There was an error updating the order');
        }
    }

}
