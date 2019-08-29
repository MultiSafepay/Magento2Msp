<?php

namespace MultiSafepay\Connect\Model\GuestCart;

use Magento\Quote\Model\QuoteIdMaskFactory;
use MultiSafepay\Connect\Api\GuestOrderInterface;

class Order implements GuestOrderInterface
{
    /**
     * @var \MultiSafepay\Connect\Model\Order
     */
    protected $orderModel;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * PaymentUrl constructor.
     * @param \MultiSafepay\Connect\Model\Order $orderModel
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        \MultiSafepay\Connect\Model\Order $orderModel,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->orderModel = $orderModel;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder($orderId, $cartId)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        if ($quoteIdMask->getQuoteId() === null) {
            return false;
        }

        return $this->orderModel->getOrder($orderId, false, $quoteIdMask->getQuoteId());
    }
}
