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

namespace MultiSafepay\Connect\Helper;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\InventorySales\Model\PlaceReservationsForSalesEvent;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class UndoCancel
{
    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;


    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param StockRegistryInterface $stockRegistry
     * @param ObjectManagerInterface $objectManager
     * @param Manager $moduleManager
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        StockRegistryInterface $stockRegistry,
        ObjectManagerInterface $objectManager,
        Manager $moduleManager,
        OrderRepositoryInterface $orderRepository,
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->stockRegistry = $stockRegistry;
        $this->objectManager = $objectManager;
        $this->moduleManager = $moduleManager;
        $this->orderRepository = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * @param Order $order
     */
    public function execute(Order $order): void
    {
        $order->setBaseDiscountCanceled(0)
            ->setBaseShippingCanceled(0)
            ->setBaseSubtotalCanceled(0)
            ->setBaseTaxCanceled(0)
            ->setBaseTotalCanceled(0)
            ->setDiscountCanceled(0)
            ->setShippingCanceled(0)
            ->setSubtotalCanceled(0)
            ->setTaxCanceled(0)
            ->setTotalCanceled(0);

        $state = 'new';
        $new_status = 'pending';

        $order->setStatus($new_status)
            ->setState($state)
            ->addStatusToHistory($new_status, 'Order has been reopened because a new transaction was started by the customer!');
        $this->orderRepository->save($order);

        if ($this->getGlobalConfig('cataloginventory/options/can_subtract')) {
            $products = $order->getAllItems();
            foreach ($products as $itemId => $product) {
                if ($this->isMSIEnabled()) {
                    $this->placeReservation($product);
                } else {
                    $stockItem = $this->stockRegistry->getStockItem($product->getProductId());
                    $new = $stockItem->getQty() - $product->getQtyOrdered();
                    $stockItem->setQty($new);
                    $stockItem->save();
                }
            }
        }

        foreach ($order->getAllItems() as $item) {
            if ($item->getQtyCanceled() > 0) {
                $item->setQtyCanceled(0);
                $this->orderItemRepository->save($item);
            }
        }
    }

    /**
     * @param $product
     */
    private function placeReservation($product)
    {
        $itemsToUndoCancel[] = $this->objectManager->create(ItemToSellInterface::class, [
            'sku' => $product->getSku(),
            'qty' => -$product->getQtyCanceled(),
        ]);

        $salesChannel = $this->getSalesChannel($product->getStore()->getWebsiteId());
        $salesEvent = $this->getSalesEvent($product->getOrderId());

        $placeReservationsForSalesEvent = $this->objectManager->create(PlaceReservationsForSalesEvent::class);
        $placeReservationsForSalesEvent->execute($itemsToUndoCancel, $salesChannel, $salesEvent);
    }

    /**
     * @param $websiteId
     * @return mixed
     */
    private function getSalesChannel($websiteId)
    {
        $websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        $websiteCode = $websiteRepository->getById($websiteId)->getCode();

        return $this->objectManager->create(SalesChannelInterface::class, [
            'data' => [
                'type' => SalesChannelInterface::TYPE_WEBSITE,
                'code' => $websiteCode
            ]
        ]);
    }

    /**
     * @param $objectId
     * @return mixed
     */
    private function getSalesEvent($objectId)
    {
        return $this->objectManager->create(SalesEventInterface::class, [
            'type' => 'order_reordered',
            'objectType' => SalesEventInterface::OBJECT_TYPE_ORDER,
            'objectId' => (string)$objectId,
        ]);
    }

    /**
     * @param $path
     * @param null $storeId
     * @return mixed
     */
    private function getGlobalConfig($path, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * @return bool
     */
    private function isMSIEnabled()
    {
        return $this->moduleManager->isEnabled('Magento_InventorySalesApi');
    }
}
