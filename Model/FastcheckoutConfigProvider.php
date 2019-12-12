<?php

namespace MultiSafepay\Connect\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Asset\Repository;

class FastcheckoutConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{

    protected $_assetRepo;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Repository $assetRepo
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_assetRepo = $assetRepo;
    }

    public function disableCheckout()
    {
        $fco_active = $this->_scopeConfig->getValue('fastcheckout/fastcheckout/fastcheckout_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $disable_checkout = $this->_scopeConfig->getValue('fastcheckout/fastcheckout/fastcheckout_disable_checkout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($fco_active && $disable_checkout) {
            return true;
        } else {
            return false;
        }
    }

    public function getConfig()
    {
        $config = [];
        $config = array_merge_recursive($config, [
            'payment' => [
                'connect' => [
                    'hide_normal_checkout' => false, //$this->disableCheckout()
                ],
            ],
        ]);
        return $config;
    }
}
