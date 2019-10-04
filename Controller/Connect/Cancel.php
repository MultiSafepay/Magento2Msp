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

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order;
use MultiSafepay\Connect\Helper\Data;
use MultiSafepay\Connect\Model\Api\MspClient;

/**
 * Responsible for loading page content.
 *
 * This is a basic controller that only loads the corresponding layout file. It may duplicate other such
 * controllers, and thus it is considered tech debt. This code duplication will be resolved in future releases.
 */
class Cancel extends \Magento\Framework\App\Action\Action
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    protected $_mspHelper;
    protected $_session;
    protected $_order;
    protected $_cartRepository;
    protected $_client;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_requestHttp;

    /**
     * @param \Magento\Framework\App\Action\Context      $context
     * @param \Magento\Framework\Registry                $coreRegistry
     * @param \Magento\Sales\Model\Order                 $order
     * @param \Magento\Checkout\Model\Session            $session
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \MultiSafepay\Connect\Helper\Data          $helperData
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Order $order,
        Session $session,
        CartRepositoryInterface $cartRepository,
        Data $helperData
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_requestHttp = $context->getRequest();
        parent::__construct($context);
        $this->_client = new MspClient();
        $this->_order = $order;
        $this->_session = $session;
        $this->_cartRepository = $cartRepository;

        $this->_mspHelper = $helperData;
    }

    public function execute()
    {
        $params = $this->_requestHttp->getParams();

        if (!$this->validateParams($params) || !$this->_mspHelper->validateOrderHash($params['transactionid'], $params['hash'])) {
            $this->_redirect('checkout/cart');
            return;
        }

        $this->_mspHelper->lockProcess('multisafepay-' . $params['transactionid']);
        $incrementId = $params['transactionid'];
        $this->_session->restoreQuote();

        /* @var $order \Magento\Sales\Model\Order */
        $order = $this->_order->loadByIncrementId($incrementId);

        if ($order->getId()) {
            try {
                $environment = $this->_mspHelper->getMainConfigData('msp_env');
                $this->_mspHelper->initializeClient($environment, $order, $this->_client);
                $orderDetails = $this->_client->orders->get('orders', $incrementId);

                /** @var \Magento\Quote\Model\Quote $quote */
                $quote = $this->_cartRepository->get($order->getQuoteId());

                $quote->setIsActive(1)->setReservedOrderId(null);
                $this->_cartRepository->save($quote);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            }
            //Cancel the order so a new one can created
            //You can disable the line below if you are using a fulfillment system that does not expect the order to be canceled,
            //but reopened again by second chance. Removing the line will keep the order pending. (PLGMAGTWOS-196)
            $order->registerCancellation('Order canceled by customer')->save();

            $message = "The transaction was canceled or declined and the order was closed, please try again.";

            $reason_code = empty($orderDetails->reason_code) ? '' : ":{$orderDetails->reason_code}";

            $reason = empty($orderDetails->reason) ? '' : "<br><ul><li>{$orderDetails->reason}{$reason_code}</li></ul>";

            $this->messageManager->addError(
                __(
                    $message . $reason
                )
            );
        }

        $this->_mspHelper->unlockProcess('multisafepay-' . $params['transactionid']);

        $this->_redirect('checkout/cart');
        return;
    }

    private function validateParams($params)
    {
        return isset($params['hash']) && isset($params['transactionid']);
    }
}
