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

namespace MultiSafepay\Connect\Model\Observers;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use MultiSafepay\Connect\Helper\Data;
use MultiSafepay\Connect\Model\Connect;

class RestoreQuote implements ObserverInterface
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_messageManager;

    /**
     * @var \MultiSafepay\Connect\Model\Connect
     */
    protected $_mspConnect;

    /**
     * @var \MultiSafepay\Connect\Helper\Data
     */
    protected $_mspData;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_session;


    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\Session $session
     * @param \MultiSafepay\Connect\Model\Connect $connect
     * @param \MultiSafepay\Connect\Helper\Data $data
     */
    public function __construct(
        ManagerInterface $messageManager,
        Session $session,
        Connect $connect,
        Data $data
    ) {
        $this->_messageManager = $messageManager;
        $this->_session = $session;
        $this->_mspConnect = $connect;
        $this->_mspData = $data;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $paymentModel = $this->_mspConnect;
        $session = $this->_session;
        $lastRealOrder = $session->getLastRealOrder();

        if ($lastRealOrder && $lastRealOrder->getPayment()) {
            $keepCartAlive = $paymentModel->getMainConfigData('keep_cart_alive', $lastRealOrder->getStoreId());
            if (!$keepCartAlive) {
                return $this;
            }
            $status = $paymentModel->getMainConfigData('order_status', $lastRealOrder->getStoreId());
            $helper = $this->_mspData;
            $state = $helper->getAssignedState($status);

            if ($lastRealOrder->getState() == $state) {
                $payment = $lastRealOrder->getPayment()->getMethodInstance();
                if (is_object($payment) && $helper->isMspGateway($payment->getCode()) && $payment->getCode() != "mspbanktransfer") {
                    $session->restoreQuote();
                }
            }
        }
        return $this;
    }
}
