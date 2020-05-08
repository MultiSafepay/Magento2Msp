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

namespace MultiSafepay\Connect\Model;

use Magento\Sales\Api\OrderRepositoryInterface;
use MultiSafepay\Connect\Api\OrderInterface;
use MultiSafepay\Connect\Helper\Data;

class Order implements OrderInterface
{

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var Data
     */
    protected $mspHelper;

    /**
     * PaymentUrl constructor.
     * @param \Magento\Sales\Model\Order $order
     * @param OrderRepositoryInterface $orderRepository
     * @param Data $data
     */
    public function __construct(
        \Magento\Sales\Model\Order $order,
        OrderRepositoryInterface $orderRepository,
        Data $data
    ) {
        $this->order = $order;
        $this->orderRepository = $orderRepository;
        $this->mspHelper = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder($orderId, $hash)
    {
        if (!$this->mspHelper->validateOrderHash($orderId, $hash)) {
            return '';
        }

        try {
            $order = $this->order->loadByIncrementId($orderId);
        } catch (\Exception $e) {
            return 'Unable to load order';
        }

        return $this->orderRepository->get($order->getId());
    }
}
