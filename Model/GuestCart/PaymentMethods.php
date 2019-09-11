<?php

namespace MultiSafepay\Connect\Model\GuestCart;

use Magento\Quote\Model\QuoteIdMaskFactory;

class PaymentMethods implements \MultiSafepay\Connect\Api\GuestPaymentMethodsInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var \MultiSafepay\Connect\Model\PaymentMethods
     */
    protected $paymentMethods;

    public function __construct(
        \MultiSafepay\Connect\Model\PaymentMethods $paymentMethods,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->paymentMethods = $paymentMethods;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($cartId)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        if ($quoteIdMask->getQuoteId() === null) {
            return false;
        }
        return $this->paymentMethods->getList($quoteIdMask->getQuoteId());
    }
}
