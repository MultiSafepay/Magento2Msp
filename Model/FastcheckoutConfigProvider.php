<?php

namespace MultiSafepay\Connect\Model;

class FastcheckoutConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{

    protected $_assetRepo;

    public function __construct(
    \Magento\Framework\ObjectManagerInterface $objectManager, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\View\Asset\Repository $assetRepo
    )
    {
        $this->_objectManager = $objectManager;
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
        $config = array();
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
