<?php

namespace MultiSafepay\Connect\Model;

use Magento\Framework\Locale\ResolverInterface;
use MultiSafepay\Connect\Helper\Data;
use MultiSafepay\Connect\Model\Config\Source\Creditcards;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ConnectConfigProvider implements
    \Magento\Checkout\Model\ConfigProviderInterface
{
    protected $_assetRepo;
    protected $_mspHelper;
    protected $_connect;
    protected $_creditcards;
    private $_scopeConfig;
    private $localeResolver;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Repository $assetRepo,
        ResolverInterface $localeResolver,
        Data $helperData,
        Connect $connect,
        Creditcards $creditcards
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_assetRepo = $assetRepo;
        $this->localeResolver = $localeResolver;
        $this->_mspHelper = $helperData;
        $this->_connect = $connect;
        $this->_creditcards = $creditcards;
    }

    public function getConfig()
    {
        $config = array();

        $config = array_merge_recursive(
            $config,
            [
            'payment' => [
                'connect' => [
                    'issuers'       => $this->getIssuers(),
                    'creditcards'   => $this->getCreditcards(),
                    'years'         => $this->getYears(),
                    'active_method' => $this->getActiveMethod(),
                    'images'        => $this->getImageURLs()
                ],
            ],
            ]
        );

        return $config;
    }

    public function getIssuers()
    {
        return $this->_connect->getIssuers();
    }

    public function getCreditcards()
    {
        $cards = $this->_creditcards;
        $creditcards = $cards->toOptionArray();
        return $creditcards;
    }

    public function getYears()
    {
        $years = [];
        for ($i = date("Y") - 17; $i > date("Y") - 125; $i--) {
            $years[] = [
                'value' => $i,
                'year'  => $i
            ];
        }
        return $years;
    }

    public function getActiveMethod()
    {
        $active_method = $this->_scopeConfig->getValue(
            'multisafepay/connect/msp_preselect_method',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $active_method;
    }

    public function getImageURLs()
    {
        //gateways
        $images = array();
        foreach ($this->_mspHelper->gateways as $key => $value) {
            $asset = $this->_assetRepo->createAsset(
                "MultiSafepay_Connect::images/" . strtolower(
                    $this->localeResolver->getLocale()
                ) . '/' . $value . ".png"
            );
            try {
                if ($asset->getSourceFile()) {
                    $images[$value] = $this->_assetRepo->getUrl(
                        "MultiSafepay_Connect::images/" . strtolower(
                            $this->localeResolver->getLocale()
                        ) . '/' . $value . ".png"
                    );
                }
            } catch (\Exception $e) {
                $images[$value] = $this->_assetRepo->getUrl(
                    "MultiSafepay_Connect::images/nl_nl/" . $value . ".png"
                );
            }
        }

        //giftcards
        foreach ($this->_mspHelper->giftcards as $key => $value) {
            $asset = $this->_assetRepo->createAsset(
                "MultiSafepay_Connect::images/" . strtolower(
                    $this->localeResolver->getLocale()
                ) . '/' . $value . ".png"
            );
            try {
                if ($asset->getSourceFile()) {
                    $images[$value] = $this->_assetRepo->getUrl(
                        "MultiSafepay_Connect::images/" . strtolower(
                            $this->localeResolver->getLocale()
                        ) . '/' . $value . ".png"
                    );
                }
            } catch (\Exception $e) {
                $images[$value] = $this->_assetRepo->getUrl(
                    "MultiSafepay_Connect::images/nl_nl/" . $value . ".png"
                );
            }
        }
        return $images;
    }
}
