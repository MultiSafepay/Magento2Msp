<?php

namespace MultiSafepay\Connect\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Runner\Exception;

class PaymentUrl implements \MultiSafepay\Connect\Api\PaymentUrlInterface
{

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * PaymentUrl constructor.
     * @param \Magento\Sales\Model\Order $order
     */
    public function __construct(
        \Magento\Sales\Model\Order $order
    ) {
        $this->order = $order;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentUrl($orderId, $customerId, $cartId = false)
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

        $paymentUrl = $order->getPayment()->getAdditionalInformation('payment_link');

        return $paymentUrl;
    }
}