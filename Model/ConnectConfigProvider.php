<?php

namespace MultiSafepay\Connect\Model;

use Magento\Framework\Locale\ResolverInterface;
use MultiSafepay\Connect\Helper\Data;

class ConnectConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{

    protected $_assetRepo;
    private $_scopeConfig;
    private $localeResolver;
    private $_objectManager;
    protected $_mspHelper;

    public function __construct(
    \Magento\Framework\ObjectManagerInterface $objectManager, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\View\Asset\Repository $assetRepo, ResolverInterface $localeResolver
    )
    {
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_assetRepo = $assetRepo;
        $this->localeResolver = $localeResolver;
        $this->_mspHelper = new \MultiSafepay\Connect\Helper\Data;
    }

    public function GetIssuers()
    {
        $connect = $this->_objectManager->create('MultiSafepay\Connect\Model\Connect');
        $issuers = $connect->getIssuers();
        return $issuers;
    }

    public function GetCreditcards()
    {
        $cards = $this->_objectManager->create('MultiSafepay\Connect\Model\Config\Source\Creditcards');
        $creditcards = $cards->toOptionArray();
        return $creditcards;
    }

    public function getImageURLs()
    {
        //gateways
        $images = array();
        foreach ($this->_mspHelper->gateways as $key => $value) {
            $asset = $this->_assetRepo->createAsset("MultiSafepay_Connect::images/" . strtolower($this->localeResolver->getLocale()) . '/' . $value . ".png");
            try {
                if ($asset->getSourceFile()) {
                    $images[$value] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/" . strtolower($this->localeResolver->getLocale()) . '/' . $value . ".png");
                }
            } catch (\Exception $e) {
                $images[$value] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/nl_nl/" . $value . ".png");
            }
        }

        //giftcards
        foreach ($this->_mspHelper->giftcards as $key => $value) {
            $asset = $this->_assetRepo->createAsset("MultiSafepay_Connect::images/" . strtolower($this->localeResolver->getLocale()) . '/' . $value . ".png");
            try {
                if ($asset->getSourceFile()) {
                    $images[$value] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/" . strtolower($this->localeResolver->getLocale()) . '/' . $value . ".png");
                }
            } catch (\Exception $e) {
                $images[$value] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/nl_nl/" . $value . ".png");
            }
        }
        return $images;
    }

    public function getActiveMethod()
    {
        $active_method = $this->_scopeConfig->getValue('multisafepay/connect/msp_preselect_method', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $active_method;
    }

    public function GetYears()
    {
        $years = [];
        for ($i = date("Y") - 17; $i > date("Y") - 125; $i--) {
            $years[] = [
                'value' => $i,
                'year' => $i
            ];
        }
        return $years;
    }

    public function getConfig()
    {
        $config = array();

        $config = array_merge_recursive($config, [
            'payment' => [
                'connect' => [
                    'issuers' => $this->GetIssuers(),
                    'creditcards' => $this->GetCreditcards(),
                    'years' => $this->GetYears(),
                    'active_method' => $this->getActiveMethod(),
                    'images' => $this->getImageURLs()
                ],
            ],
        ]);

        return $config;
    }

}
