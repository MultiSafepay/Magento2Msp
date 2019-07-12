<?php

namespace MultiSafepay\Connect\Model\GuestCart;

class PaymentUrl implements \MultiSafepay\Connect\Api\GuestPaymentUrlInterface
{
    /**
     * @var \MultiSafepay\Connect\Model\PaymentUrl
     */
    protected $paymentUrlModel;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * PaymentUrl constructor.
     * @param \MultiSafepay\Connect\Model\PaymentUrl $paymentUrlModel
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        \MultiSafepay\Connect\Model\PaymentUrl $paymentUrlModel,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->paymentUrlModel = $paymentUrlModel;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentUrl($orderId, $cartId)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->paymentUrlModel->getPaymentUrl($orderId, $quoteIdMask->getQuoteId());
    }
}