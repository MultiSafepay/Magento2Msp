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
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order;
use MultiSafepay\Connect\Model\Connect;

/**
 * Responsible for loading page content.
 *
 * This is a basic controller that only loads the corresponding layout file. It may duplicate other such
 * controllers, and thus it is considered tech debt. This code duplication will be resolved in future releases.
 */
class Redirect extends Action
{
    protected $_session;
    protected $_order;
    protected $_mspConnect;

    protected $_cartRepo;

    /**
     * Redirect constructor.
     * @param Context $context
     * @param Session $session
     * @param Order $order
     * @param CartRepositoryInterface $cartRepository
     * @param Connect $mspConnect
     */
    public function __construct(
        Context $context,
        Session $session,
        Order $order,
        CartRepositoryInterface $cartRepository,
        Connect $mspConnect
    ) {
        parent::__construct($context);
        $this->_session = $session;
        $this->_order = $order;
        $this->_mspConnect = $mspConnect;
        $this->_cartRepo = $cartRepository;
    }

    public function execute()
    {
        $session = $this->_session;
        $order = $this->_order;
        $paymentMethod = $this->_mspConnect;

        $order->load($this->_session->getlastOrderId());

        $transactionObject = $paymentMethod->transactionRequest($order, false);

        if ($order->getId()) {
            /** @var CartRepositoryInterface $quoteRepository */
            $quoteRepository = $this->_cartRepo;
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $quoteRepository->get($order->getQuoteId());
            $quote->setIsActive(1)->setReservedOrderId(null);
            $quoteRepository->save($quote);
        }

        if (!empty($transactionObject->result->error_code) || !$transactionObject) {
            if (!$transactionObject) {
                $this->messageManager->addError(__('There was an error processing your transaction request, please try again with another payment method.'));
            } else {
                $this->messageManager->addError(__('There was an error processing your transaction request, please try again with another payment method.') . ' ' . __('Error: ' . $transactionObject->result->error_code . ' - ' . $transactionObject->result->error_info));
            }
            $session->restoreQuote();
            $this->_redirect('checkout/cart');
        } else {
            if (!empty($transactionObject->result->data->payment_details->type)) {
                if ($transactionObject->result->data->payment_details->type == "BANKTRANS") {
                    $this->getResponse()->setRedirect($paymentMethod->banktransurl);
                } else {
                    $this->getResponse()->setRedirect($transactionObject->result->data->payment_url);
                }
            } else {
                $this->getResponse()->setRedirect($transactionObject->result->data->payment_url);
            }
        }
    }
}
