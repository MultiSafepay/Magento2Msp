<?php

namespace MultiSafepay\Connect\Api;

interface GuestPaymentMethodsInterface
{
    /**
     * GET for payment-methods api
     * @param string $cartId The cart ID.
     * @return \Magento\Quote\Api\Data\PaymentMethodInterface[] Array of payment methods.
     */
    public function getList($cartId);
}
