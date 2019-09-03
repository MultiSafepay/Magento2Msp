<?php

namespace MultiSafepay\Connect\Api;

interface RestoreQuoteInterface
{
    /**
     * Restore quote
     * @param string $orderId
     * @param string $hash
     * @return string masked_id
     */
    public function restoreQuote($orderId, $hash);
}
