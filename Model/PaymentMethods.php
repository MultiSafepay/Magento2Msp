<?php

namespace MultiSafepay\Connect\Model;

use Magento\Payment\Model\MethodList;
use Magento\Quote\Api\CartRepositoryInterface;
use MultiSafepay\Connect\Api\PaymentMethodsInterface;
use MultiSafepay\Connect\Helper\Data;

class PaymentMethods implements PaymentMethodsInterface
{

    /**
     * @var Data
     */
    protected $mspHelper;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var MethodList
     */
    protected $methodList;

    /**
     * PaymentUrl constructor.
     * @param Data $data
     * @param CartRepositoryInterface $quoteRepository
     * @param MethodList $methodList
     */
    public function __construct(
        Data $data,
        CartRepositoryInterface $quoteRepository,
        MethodList $methodList
    ) {
        $this->mspHelper = $data;
        $this->quoteRepository = $quoteRepository;
        $this->methodList = $methodList;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($cartId)
    {
        $quote = $this->quoteRepository->get($cartId);
        $methods = $this->methodList->getAvailableMethods($quote);
        foreach ($methods as $key => $value) {
            // Hide non MultiSafepay methods
            if (!property_exists($value, '_gatewayCode')) {
                unset($methods[$key]);
            }
            // Add iDEAL issuers
            if ($value->getCode() === 'ideal' && $value->getTitle() === 'iDEAL') {
                $ideal = [
                    'code' => 'ideal',
                    'title' => 'iDEAL',
                    'issuers' => $this->mspHelper->getIssuers()
                ];
                $methods[$key] = $ideal;
            }
        }
        return $methods;
    }
}
