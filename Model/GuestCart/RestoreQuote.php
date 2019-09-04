<?php

namespace MultiSafepay\Connect\Model\GuestCart;

class RestoreQuote implements \MultiSafepay\Connect\Api\GuestRestoreQuoteInterface
{
    /**
     * @var \MultiSafepay\Connect\Model\RestoreQuote
     */
    protected $restoreQuoteModel;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * PaymentUrl constructor.
     * @param \MultiSafepay\Connect\Model\RestoreQuote $restoreQuoteModel
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        \MultiSafepay\Connect\Model\RestoreQuote $restoreQuoteModel,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->restoreQuoteModel = $restoreQuoteModel;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function restoreQuote($orderId, $cartId)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        if ($quoteIdMask->getQuoteId() === null) {
            return false;
        }

        return $this->restoreQuoteModel->restoreQuote($orderId, false, $quoteIdMask->getQuoteId());
    }
}
