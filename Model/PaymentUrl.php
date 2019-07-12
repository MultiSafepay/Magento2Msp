<?php

namespace MultiSafepay\Connect\Model;

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
    public function getPaymentUrl($orderId, $cartId)
    {
        $order = $this->order->load($orderId);
        $quoteId = $order->getQuoteId();

        if ($quoteId != $cartId) {
            return '';
        }

        $paymentUrl = $order->getPayment()->getAdditionalInformation('payment_link');

        return $paymentUrl;
    }
}