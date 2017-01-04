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

namespace MultiSafepay\Connect\Controller\Connect;

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

    /**
     * @var \Magento\Authorizenet\Helper\DataFactory
     */
    protected $dataFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_requestHttp;

    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Framework\Registry $coreRegistry, \Magento\Authorizenet\Helper\DataFactory $dataFactory
    )
    {
        $this->_coreRegistry = $coreRegistry;
        $this->dataFactory = $dataFactory;
        $this->_requestHttp = $context->getRequest();
        parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->_requestHttp->getParams();
        if (isset($params['transactionid'])) {
            $incrementId = $params['transactionid'];
        }


        if ($incrementId) {
            /* @var $order \Magento\Sales\Model\Order */
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($incrementId);

            if ($order->getId()) {
                try {

                    /** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
                    $quoteRepository = $this->_objectManager->create('Magento\Quote\Api\CartRepositoryInterface');
                    /** @var \Magento\Quote\Model\Quote $quote */
                    $quote = $quoteRepository->get($order->getQuoteId());

                    $quote->setIsActive(1)->setReservedOrderId(null);
                    $quoteRepository->save($quote);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    
                }
                //Cancel the order so a new one can created
                $order->registerCancellation('Order cancelled by customer')->save();
            }
        }



        $this->_redirect('checkout/cart');
        return;
    }

}
