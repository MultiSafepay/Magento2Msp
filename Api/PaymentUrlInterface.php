<?php


namespace MultiSafepay\Connect\Api;

interface PaymentUrlInterface
{

    /**
     * GET for paymentUrl api
     * @param string $orderId
     * @param string $cartId
     * @return string
     */
    public function getPaymentUrl($orderId, $cartId);
}