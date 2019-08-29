<?php

namespace MultiSafepay\Connect\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use MultiSafepay\Connect\Api\OrderInterface;
use PHPUnit\Runner\Exception;

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
     * PaymentUrl constructor.
     * @param \Magento\Sales\Model\Order $order
     */
    public function __construct(
        \Magento\Sales\Model\Order $order,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->order = $order;
        $this->orderRepository = $orderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder($orderId, $customerId, $cartId = false)
    {
        try {
            $order = $this->order->load($orderId);
        } catch (NoSuchEntityException $e) {
            return 'Cannot find order';
        } catch (Exception $e) {
            return 'Unable to load order';
        }

        if ($cartId && $order->getQuoteId() != $cartId) {
            return false;
        }

        /**
         * This is already checked on the order load
         * \Magento\Sales\Model\ResourceModel\Order\Plugin\Authorization::afterLoad
        */
        if ($customerId && $customerId != $order->getCustomerId()) {
            return '';
        }

        return $this->orderRepository->get($orderId);
    }
}
