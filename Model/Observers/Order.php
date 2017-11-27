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

namespace MultiSafepay\Connect\Model\Observers;

use Magento\Framework\Event\ObserverInterface;

class Order implements ObserverInterface
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /*
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_messageManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, \Magento\Framework\Message\ManagerInterface $messageManager)
    {
        $this->_objectManager = $objectManager;
        $this->_messageManager = $messageManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $paymentMethod = $this->_objectManager->create('MultiSafepay\Connect\Model\Connect');
        /** @var $event Varien_Event */
        $event = $observer->getEvent();

        $orderId = $observer->getEvent()->getOrder()->getId();


        /** @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();

        $app_state = $this->_objectManager->get('\Magento\Framework\App\State');
        $area_code = $app_state->getAreaCode();
        if ($app_state->getAreaCode() != \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            return $this;
        } else {
            $paymentMethod->_isAdmin = true;
        }

        if ($order->getEditIncrement()) {
            return $this;
        }

        $payment = $order->getPayment()->getMethodInstance();

        if (!in_array($payment->getCode(), $this->_objectManager->create('MultiSafepay\Connect\Helper\Data')->gateways)) {
            return $this;
        }
        
        if(!$paymentMethod->getMainConfigData('create_paylink', $order->getStoreId())){
	    return $this;
        }
        
        $paymentMethod->_manualGateway = $payment->_gatewayCode;

        $productRepo = $this->_objectManager->create('Magento\Catalog\Model\Product');

        $transactionObject = $paymentMethod->transactionRequest($order, $productRepo);

        if (!empty($transactionObject->result->error_code)) {
            $this->_messageManager->addError(__('There was an error processing your transaction request, please try again with another payment method. Error: ' . $transactionObject->result->error_code . ' - ' . $transactionObject->result->error_info));
        }
        return $this;
    }

}
