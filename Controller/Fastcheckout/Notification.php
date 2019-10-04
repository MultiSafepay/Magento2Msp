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

namespace MultiSafepay\Connect\Controller\Fastcheckout;

use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order;

use MultiSafepay\Connect\Helper\Data;
use MultiSafepay\Connect\Model\Connect;
use MultiSafepay\Connect\Model\Fastcheckout;

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

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_requestHttp;
    protected $_mspHelper;
    protected $_mspConnect;
    protected $_mspFastcheckout;
    protected $_invoiceSender;
    protected $_storeManagerInterface;
    protected $_stockRegistryProviderInterface;
    protected $_order;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        InvoiceSender $invoiceSender,
        StoreManagerInterface $storeManagerInterface,
        StockRegistryProviderInterface $stockRegistryProviderInterface,
        Order $order,
        Data $data,
        Connect $connect,
        Fastcheckout $fastcheckout
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_requestHttp = $context->getRequest();
        parent::__construct($context);
        $this->_invoiceSender = $invoiceSender;
        $this->_storeManagerInterface = $storeManagerInterface;
        $this->_stockRegistryProviderInterface = $stockRegistryProviderInterface;
        $this->_mspHelper = $data;
        $this->_mspConnect = $connect;
        $this->_mspFastcheckout = $fastcheckout;
        $this->_order = $order;
    }

    public function execute()
    {
        $params = $this->_requestHttp->getParams();

        if (!$this->validateParams($params)) {
            return false;
        }

        $this->_mspHelper->lockProcess('multisafepay-' . $params['transactionid']);
        $paymentMethod = $this->_mspFastcheckout;

        $isShipping = false;

        if (isset($params['type'])) {
            $isShipping = ($params['type'] == 'shipping') ? true : false;
        }

        // Is this notification about shipping rates?
        if ($isShipping) {
            print_r($paymentMethod->getShippingRates($params));
            $this->_mspHelper->unlockProcess(
                'multisafepay-' . $params['transactionid']
            );
            return;
        }

        $order_id = $paymentMethod->notification($params);
        $paymentMethod = $this->_mspConnect;
        $paymentMethod->_invoiceSender = $this->_invoiceSender;
        $storeManager = $this->_storeManagerInterface;
        $paymentMethod->_stockInterface = $this->_stockRegistryProviderInterface;

        $order = $this->_order;
        $order_information = $order->load($order_id);

        if (!is_null($order_information->getId())) {
            $gateway = $order_information->getPayment()->getMethod();
            if ($this->_mspHelper->isMspGateway($gateway)
                || $this->_mspHelper->isMspGiftcard($gateway)
            ) {
                $updated = $paymentMethod->notification($order_information);
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
                $this->getResponse()->setContent(
                    'Non Msp order'
                );
            }
        } else {
            $this->getResponse()->setContent(
                'Order not found'
            );
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
