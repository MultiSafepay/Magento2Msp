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

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Checkout\Model\Session;
use MultiSafepay\Connect\Model\Fastcheckout;

/**
 * Responsible for loading page content.
 *
 * This is a basic controller that only loads the corresponding layout file. It may duplicate other such
 * controllers, and thus it is considered tech debt. This code duplication will be resolved in future releases.
 */
class Redirect extends Action
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

    protected $_session;
    protected $_mspFastcheckout;

    /**
     * Redirect constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Session $session
     * @param Fastcheckout $fastcheckout
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Session $session,
        Fastcheckout $fastcheckout
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_requestHttp = $context->getRequest();
        parent::__construct($context);

        $this->_mspFastcheckout = $fastcheckout;
        $this->_session = $session;
    }

    public function execute()
    {
        $session = $this->_session;
        $paymentMethod = $this->_mspFastcheckout;

        $transactionObject = $paymentMethod->transactionRequest($session, false);

        if (!empty($transactionObject->result->error_code)) {
            $this->messageManager->addError(__('There was an error processing your transaction request, please try again with another payment method.') . ' ' . __('Error: ' . $transactionObject->result->error_code . ' - ' . $transactionObject->result->error_info));
            $this->_redirect('checkout/cart');
        } else {
            $this->getResponse()->setRedirect($transactionObject->result->data->payment_url);
        }
    }
}
