<?php

namespace MultiSafepay\Connect\Api;

interface OrderInterface
{
    /**
     * GET for order api
     * @param int $orderId
     * @param int $customerId
     * @return \Magento\Sales\Api\Data\OrderInterface Order interface.
     */
    public function getOrder($orderId, $customerId);
}
