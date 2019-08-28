<?php

namespace MultiSafepay\Connect\Api;

interface GuestPaymentUrlInterface
{
    /**
     * GET for paymentUrl api
     * @param int $orderId
     * @param string $cartId
     * @return string
     */
    public function getPaymentUrl($orderId, $cartId);
}
