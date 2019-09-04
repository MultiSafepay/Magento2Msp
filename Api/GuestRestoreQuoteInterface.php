<?php

namespace MultiSafepay\Connect\Api;

interface GuestRestoreQuoteInterface
{
    /**
     * Restore quote
     * @param int $orderId
     * @param string $cartId
     * @return string masked_id
     */
    public function restoreQuote($orderId, $cartId);
}
