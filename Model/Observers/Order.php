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
 * @copyright   Copyright (c) 2018 MultiSafepay, Inc. (https://www.multisafepay.com)
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

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use MultiSafepay\Connect\Helper\Data;
use MultiSafepay\Connect\Model\Connect;

class Order implements ObserverInterface
{
    protected $_mspConnect;
    protected $_state;
    protected $_mspData;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /*
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_messageManager;

    /**
     * @param ManagerInterface $messageManager
     * @param State $state
     * @param ProductRepositoryInterface $productRepository
     * @param Connect $connect
     * @param Data $data
     */
    public function __construct(
        ManagerInterface $messageManager,
        State $state,
        ProductRepositoryInterface $productRepository,
        Connect $connect,
        Data $data
    ) {
        $this->_messageManager = $messageManager;
        $this->_mspConnect = $connect;
        $this->_mspData = $data;
        $this->productRepository = $productRepository;
        $this->_state = $state;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $paymentMethod = $this->_mspConnect;
        /** @var $event Varien_Event */
        $event = $observer->getEvent();

        $orderId = $observer->getEvent()->getOrder()->getId();


        /** @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();

        $app_state = $this->_state;
        $area_code = $app_state->getAreaCode();
        if ($app_state->getAreaCode() != FrontNameResolver::AREA_CODE) {
            return $this;
        } else {
            $paymentMethod->_isAdmin = true;
        }

        if ($order->getEditIncrement()) {
            return $this;
        }

        $payment = $order->getPayment()->getMethodInstance();

        if (!$this->_mspData->isMspGateway($payment->getCode())) {
            return $this;
        }

        if (!$paymentMethod->getMainConfigData('create_paylink', $order->getStoreId())) {
            return $this;
        }

        $resetGateway = $paymentMethod->getMainConfigData('reset_paylink_gateway', $order->getStoreId());

        $paymentMethod->_manualGateway = $payment->_gatewayCode;

        $transactionObject = $paymentMethod->transactionRequest($order, $this->productRepository, $resetGateway);

        if (!empty($transactionObject->result->error_code)) {
            $this->_messageManager->addError(__('There was an error processing your transaction request, please try again with another payment method. Error: ' . $transactionObject->result->error_code . ' - ' . $transactionObject->result->error_info));
        }
        return $this;
    }
}
