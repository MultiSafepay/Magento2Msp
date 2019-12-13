<?php

namespace MultiSafepay\Connect\Api;

interface PaymentMethodsInterface
{
    /**
     * GET for payment-methods api
     * @param int $cartId The cart ID.
     * @return \Magento\Quote\Api\Data\PaymentMethodInterface[] Array of payment methods.
     */
    public function getList($cartId);
}
