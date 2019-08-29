<?php

namespace MultiSafepay\Connect\Api;

interface GuestOrderInterface
{
    /**
     * GET for order api
     * @param int $orderId
     * @param string $cartId
     * @return \Magento\Sales\Api\Data\OrderInterface Order interface.
     */
    public function getOrder($orderId, $cartId);
}
