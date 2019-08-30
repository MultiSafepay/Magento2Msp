<?php

namespace MultiSafepay\Connect\Api;

interface OrderInterface
{
    /**
     * GET for order api
     * @param string $orderId
     * @param string $hash
     * @return \Magento\Sales\Api\Data\OrderInterface Order interface.
     */
    public function getOrder($orderId, $hash);
}
