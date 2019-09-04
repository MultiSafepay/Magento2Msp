<?php

namespace MultiSafepay\Connect\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use PHPUnit\Runner\Exception;

class RestoreQuote implements \MultiSafepay\Connect\Api\RestoreQuoteInterface
{

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param CartRepositoryInterface $quoteRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        \Magento\Sales\Model\Order $order,
        CartRepositoryInterface $quoteRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->order = $order;
        $this->quoteRepository = $quoteRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function restoreQuote($orderId, $customerId, $cartId = false)
    {
        //var_dump($orderId);exit;
        try {
            $order = $this->order->loadByIncrementId($orderId);
        } catch (NoSuchEntityException $e) {
            return 'Cannot find order';
        } catch (Exception $e) {
            return 'Unable to load order';
        }

        if ($cartId && $order->getQuoteId() != $cartId) {
            return false;
        }

        /**
         * This is already checked on the order load
         * \Magento\Sales\Model\ResourceModel\Order\Plugin\Authorization::afterLoad
         */
        if ($customerId && $customerId != $order->getCustomerId()) {
            return '';
        }

        /* restore quote */
        $quote = $this->quoteRepository->get($order->getQuoteId());
        $quote->setIsActive(1)->setReservedOrderId(null);
        $this->quoteRepository->save($quote);

        return true;
    }
}
