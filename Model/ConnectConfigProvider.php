<?php

namespace MultiSafepay\Connect\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Asset\Repository;
use MultiSafepay\Connect\Helper\Data;
use MultiSafepay\Connect\Model\Config\Source\Creditcards;
use MultiSafepay\Connect\Model\MultisafepayTokenizationFactory;
use Magento\Customer\Model\Session;

class ConnectConfigProvider implements
    \Magento\Checkout\Model\ConfigProviderInterface
{
    protected $_assetRepo;
    protected $_mspHelper;
    protected $_connect;
    protected $_creditcards;
    protected $_mspToken;
    protected $_customerSession;
    private $_scopeConfig;
    private $localeResolver;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Repository $assetRepo,
        ResolverInterface $localeResolver,
        Data $helperData,
        Connect $connect,
        Session $customerSession,
        Creditcards $creditcards,
        MultisafepayTokenizationFactory $multisafepayTokenizationFactory
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_assetRepo = $assetRepo;
        $this->localeResolver = $localeResolver;
        $this->_mspHelper = $helperData;
        $this->_connect = $connect;
        $this->_creditcards = $creditcards;
        $this->_mspToken = $multisafepayTokenizationFactory;

        $this->_customerSession = $customerSession;
    }

    public function getConfig()
    {
        $config = [];

        $config = array_merge_recursive(
            $config,
            [
            'payment' => [
                'connect' => [
                    'recurrings' => [
                        'enabled' => $this->_mspHelper->isEnabled('tokenization'),
                        'visa' => [
                            'hasRecurrings' => $this->hasRecurrings('VISA'),
                            'recurrings'    => $this->getRecurrings('VISA'),
                        ],
                        'mastercard' => [
                            'hasRecurrings' => $this->hasRecurrings('MASTERCARD'),
                            'recurrings'    => $this->getRecurrings('MASTERCARD'),
                        ],
                        'americanexpress' => [
                            'hasRecurrings' => $this->hasRecurrings('AMEX'),
                            'recurrings'    => $this->getRecurrings('AMEX'),
                        ],


                    ],
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

    public function getRecurrings($gateway)
    {
        $recurrings = [];
        $customerID = $this->_customerSession->getCustomer()->getId();
        $recurringIds = $this->_mspHelper->getRecurringIdsByCustomerId(
            $customerID
        );

        $recurringIds = $this->_mspHelper->hideRecurringExpiredIds($recurringIds);

        foreach ($recurringIds as $id) {

            $data = $this->_mspToken->create()->load($id);

            if (strtolower($data['cc_type']) == strtolower($gateway)) {
                array_push($recurrings, $this->_mspToken->create()->load($id));
            }

        }
        $recurrings = $this->_mspToken->create()->toOptionArray($recurrings);

        return $recurrings;
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
        $images = [];
        foreach ($this->_mspHelper->gateways as $gatewayCode => $value) {
            if ($this->_mspHelper->isMspGateway($gatewayCode)) {
                $asset = $this->_assetRepo->createAsset(
                    "MultiSafepay_Connect::images/" . strtolower(
                        $this->localeResolver->getLocale()
                    ) . '/' . $gatewayCode . ".png"
                );
                try {
                    if ($asset->getSourceFile()) {
                        $images[$gatewayCode] = $this->_assetRepo->getUrl(
                            "MultiSafepay_Connect::images/" . strtolower(
                                $this->localeResolver->getLocale()
                            ) . '/' . $gatewayCode . ".png"
                        );
                    }
                } catch (\Exception $e) {
                    $images[$gatewayCode] = $this->_assetRepo->getUrl(
                        "MultiSafepay_Connect::images/nl_nl/" . $gatewayCode . ".png"
                    );
                }
            }
        }

        //giftcards
        foreach ($this->_mspHelper->gateways as $giftcardCode => $value) {
            if ($this->_mspHelper->isMspGiftcard($giftcardCode)) {
                $asset = $this->_assetRepo->createAsset(
                    "MultiSafepay_Connect::images/" . strtolower(
                        $this->localeResolver->getLocale()
                    ) . '/' . $giftcardCode . ".png"
                );
                try {
                    if ($asset->getSourceFile()) {
                        $images[$giftcardCode] = $this->_assetRepo->getUrl(
                            "MultiSafepay_Connect::images/" . strtolower(
                                $this->localeResolver->getLocale()
                            ) . '/' . $giftcardCode . ".png"
                        );
                    }
                } catch (\Exception $e) {
                    $images[$giftcardCode] = $this->_assetRepo->getUrl(
                        "MultiSafepay_Connect::images/nl_nl/" . $giftcardCode . ".png"
                    );
                }
            }
        }
        return $images;
    }

    public function hasRecurrings($gateway, $array = null)
    {

        if (empty($array) || is_null($array)) {
            $array = [];
            $customerID = $this->_customerSession->getCustomer()->getId();
            $recurringIds = $this->_mspHelper->getRecurringIdsByCustomerId($customerID);
            foreach ($recurringIds as $id) {
                array_push($array, $this->_mspToken->create()->load($id));
            }
        }

        foreach ($array as $item) {
            if (strtolower($item['cc_type']) === strtolower($gateway)) {
                return true;
            }
        }
        return false;
    }
}
