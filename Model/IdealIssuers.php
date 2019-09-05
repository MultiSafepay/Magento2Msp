<?php

namespace MultiSafepay\Connect\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use MultiSafepay\Connect\Api\IdealIssuersInterface;
use MultiSafepay\Connect\Helper\Data;
use PHPUnit\Runner\Exception;

class IdealIssuers implements IdealIssuersInterface
{
    /**
     * @var Data
     */
    protected $mspHelper;

    /**
     * IdealIssuers constructor.
     * @param Data $data
     */
    public function __construct(
        Data $data
    ) {
        $this->mspHelper = $data;
    }

    /**
     * @return false|string
     */
    public function getIssuers()
    {
        return json_encode($this->mspHelper->getIssuers());
    }
}
