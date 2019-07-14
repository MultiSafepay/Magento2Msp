<?php

namespace MultiSafepay\Connect\Api;

interface PaymentUrlInterface
{
    /**
     * GET for paymentUrl api
     * @param int $orderId
     * @param int $customerId
     * @return string
     */
    public function getPaymentUrl($orderId, $customerId);
}