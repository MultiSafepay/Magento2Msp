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

use Magento\Framework\Event\ObserverInterface;
use MultiSafepay\Connect\Model\Connect;
use MultiSafepay\Connect\Helper\Data;
use Magento\Framework\Message\ManagerInterface;

class Shipment implements ObserverInterface
{


    /*
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_messageManager;

    /*
     * @var \MultiSafepay\Connect\Model\Connect
     */
    protected $_mspConnect;

    /*
     * @var \MultiSafepay\Connect\Helper\Data
     */
    protected $_mspData;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \MultiSafepay\Connect\Model\Connect $connect
     * @param \MultiSafepay\Connect\Helper\Data $data
     */
    public function __construct(
        ManagerInterface $messageManager,
        Connect $connect,
        Data $data
    ) {
        $this->_messageManager = $messageManager;
        $this->_mspConnect = $connect;
        $this->_mspData = $data;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $paymentMethod =$this->_mspConnect;
        $event = $observer->getEvent();
        $shipment = $event->getShipment();
        $order = $shipment->getOrder();
        $payment = $order->getPayment()->getMethodInstance();

        if (!$this->_mspData->isMspGateway($payment->getCode())) {
            return $this;
        }

        $shipped = $paymentMethod->shipOrder($order);
        if ($shipped['success']) {
            $this->_messageManager->addSuccess(__('Your shipment has been processed. Your transaction has also been updated at MultiSafepay'));
        } elseif ($shipped['error']) {
            $this->_messageManager->addError(__('Your shipment has been processed, but the transaction could not be updated at MultiSafepay. If needed you need to update your transaction manually using MultiSafepay Control'));
        }
        return $this;
    }
}
