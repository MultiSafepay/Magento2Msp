<?php

namespace MultiSafepay\Connect\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use MultiSafepay\Connect\Api\RestoreQuoteInterface;
use MultiSafepay\Connect\Helper\Data;
use PHPUnit\Runner\Exception;

class RestoreQuote implements RestoreQuoteInterface
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
     * @var Data
     */
    protected $mspHelper;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param CartRepositoryInterface $quoteRepository
     * @param Data $data
     */
    public function __construct(
        \Magento\Sales\Model\Order $order,
        CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        Data $data
    ) {
        $this->order = $order;
        $this->quoteRepository = $quoteRepository;
        $this->mspHelper = $data;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function restoreQuote($orderId, $hash)
    {
        if (!$this->mspHelper->validateOrderHash($orderId, $hash)) {
            return '';
        }

        try {
            $order = $this->order->loadByIncrementId($orderId);
        } catch (NoSuchEntityException $e) {
            return 'Cannot find order';
        } catch (Exception $e) {
            return 'Unable to load order';
        }

        $quote = $this->quoteRepository->get($order->getQuoteId());
        $quote->setIsActive(1)->setReservedOrderId(null);
        $this->quoteRepository->save($quote);


        if ($order->getCustomerIsGuest()) {
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($order->getQuoteId(), 'quote_id');
            return $quoteIdMask->getMaskedId();
        }
        return true;
    }
}
