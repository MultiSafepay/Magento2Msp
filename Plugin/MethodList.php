<?php

namespace MultiSafepay\Connect\Plugin;

use MultiSafepay\Connect\Helper\Data;

class MethodList
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
     * @param \Magento\Payment\Model\MethodList $subject
     * @param $result
     * @return mixed
     */
    public function afterGetAvailableMethods(\Magento\Payment\Model\MethodList $subject, $result)
    {
        foreach ($result as $key => $value) {
            if ($value->getCode() === 'ideal' && $value->getTitle() === 'iDEAL') {
                $ideal = ['code' => 'ideal', 'title' => 'iDEAL'];
                $idealExtended = array_merge($ideal, $this->mspHelper->getIssuers());
                $result[$key] = $idealExtended;
            }
        }
        return $result;
    }
}
