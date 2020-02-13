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
use MultiSafepay\Connect\Model\Connect;
use MultiSafepay\Connect\Helper\Data;

/**
 * Responsible for loading page content.
 *
 * This is a basic controller that only loads the corresponding layout file. It may duplicate other such
 * controllers, and thus it is considered tech debt. This code duplication will be resolved in future releases.
 */
class Success extends \Magento\Framework\App\Action\Action
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    protected $_order;
    protected $_session;
    protected $_invoiceSender;

    protected $_mspHelper;
    protected $_mspConnect;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_requestHttp;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Order $order,
        Session $session,
        InvoiceSender $invoiceSender,
        Connect $connect,
        Data $data
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_requestHttp = $context->getRequest();
        $this->_order = $order;
        $this->_session = $session;
        $this->_invoiceSender = $invoiceSender;

        $this->_mspConnect = $connect;
        $this->_mspHelper = $data;
    }

    public function execute()
    {
        $params = $this->_requestHttp->getParams();

        if (!$this->validateParams($params) || !$this->_mspHelper->validateOrderHash($params['transactionid'], $params['hash'])) {
            $this->_redirect('checkout/cart');
            return;
        }
        $this->_mspHelper->lockProcess('multisafepay-' . $params['transactionid']);
        $session = $this->_session;

        $order = $this->_order;
        $order_information = $order->loadByIncrementId($params['transactionid']);

        $session->unsQuoteId();
        $session->getQuote()->setIsActive(false)->save();

        // set some vars for the success page
        $session->setLastSuccessQuoteId($order_information->getQuoteId());
        $session->setLastQuoteId($order_information->getQuoteId());
        $session->setLastOrderId($order_information->getEntityId());
        $session->setLastRealOrderId($order_information->getIncrementId());

        //To a status request in order to update the order before redirect to thank you page. Doing this the status won't be payment pending so the order page can be viewed
        $paymentMethod = $this->_mspConnect;
        $paymentMethod->_invoiceSender = $this->_invoiceSender;
        $paymentMethod->notification($order_information, true);

        $this->_mspHelper->unlockProcess('multisafepay-' . $params['transactionid']);
        $this->_redirect('checkout/onepage/success?utm_nooverride=1');
        return;
    }

    private function validateParams($params)
    {
        return isset($params['hash']) && isset($params['transactionid']);
    }
}
