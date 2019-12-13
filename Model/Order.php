<?php

namespace MultiSafepay\Connect\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use MultiSafepay\Connect\Api\OrderInterface;
use MultiSafepay\Connect\Helper\Data;
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
        } catch (NoSuchEntityException $e) {
            return 'Cannot find order';
        } catch (Exception $e) {
            return 'Unable to load order';
        }

        return $this->orderRepository->get($order->getId());
    }
}
