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

namespace MultiSafepay\Connect\Controller\Connect;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;

use MultiSafepay\Connect\Helper\Data;
use MultiSafepay\Connect\Model\Connect;
use MultiSafepay\Connect\Model\MultisafepayTokenizationFactory;

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
    protected $_mspConnect;
    protected $_session;
    protected $_order;
    protected $_invoiceSender;
    protected $_storeManager;
    protected $_stockRegistryProvider;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_requestHttp;

    public $_mspToken;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Session $session,
        Order $order,
        Data $data,
        Connect $connect,
        InvoiceSender $invoiceSender,
        StoreManagerInterface $storeManager,
        StockRegistryProviderInterface $stockRegistryProvider,
        MultisafepayTokenizationFactory $tokenizationFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_requestHttp = $context->getRequest();
        parent::__construct($context);
        $this->_order = $order;
        $this->_invoiceSender = $invoiceSender;
        $this->_storeManager = $storeManager;
        $this->_session = $session;
        $this->_mspHelper = $data;
        $this->_mspConnect = $connect;
        $this->_stockRegistryProvider = $stockRegistryProvider;

        $this->_mspToken = $tokenizationFactory;
    }

    public function execute()
    {
        $params = $this->_requestHttp->getParams();

        if (!$this->validateParams($params)) {
            return false;
        }

        if (isset($params['hash'])) {
            $recurringId = $this->_mspHelper->getRecurringIdByHash($params['hash']);
            $this->_mspToken->create()->load($recurringId)->delete();
            return true;
        }
        $this->_mspHelper->lockProcess('multisafepay-' . $params['transactionid']);
        if (!isset($params['timestamp'])) {
            $this->getResponse()->setContent('No timestamp is set so we are stopping the callback');
            $this->_mspHelper->unlockProcess('multisafepay-' . $params['transactionid']);
            return false;
        }
        $session = $this->_session;
        $order = $this->_order;
        $order_information = $order->loadByIncrementId($params['transactionid']);

        if (!is_null($order_information->getId())) {
            $gateway = $order_information->getPayment()->getMethod();
            if ($this->_mspHelper->isMspGateway($gateway) || $this->_mspHelper->isMspGiftcard($gateway)) {
                $paymentMethod = $this->_mspConnect;
                $paymentMethod->_invoiceSender = $this->_invoiceSender;
                $storeManager = $this->_storeManager;
                $paymentMethod->_stockInterface = $this->_stockRegistryProvider;

                $updated = $paymentMethod->notification($order);
                $this->_mspHelper->unlockProcess(
                    'multisafepay-' . $params['transactionid']
                );
                if ($updated) {
                    if (isset($params['type'])
                        && $params['type'] == 'initial'
                    ) {
                        $this->getResponse()->setContent(
                            '<a href="' . $storeManager->getStore()->getBaseUrl(
                            )
                            . 'multisafepay/connect/success?transactionid='
                            . $params['transactionid']
                            . '"> Return back to the webshop</a>'
                        );
                    } else {
                        $this->getResponse()->setContent('ok');
                    }
                } else {
                    $this->getResponse()->setContent(
                        'There was an error updating the order'
                    );
                }
            } else {
                $this->getResponse()->setContent('Non Msp order');
            }
        } else {
            $this->getResponse()->setContent('Order not found');
        }
    }

    /**
     * @param array $params
     *
     * @return bool
     */
    private function validateParams($params)
    {
        return isset($params['transactionid']);
    }
}
