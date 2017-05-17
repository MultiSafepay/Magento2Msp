<?php

namespace MultiSafepay\Connect\Model;
use Magento\Framework\Locale\ResolverInterface;

class ConnectConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{

    protected $_assetRepo;
    private $_scopeConfig;
    private $localeResolver;
    private $_objectManager;

    public function __construct(
    \Magento\Framework\ObjectManagerInterface $objectManager, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\View\Asset\Repository $assetRepo, ResolverInterface $localeResolver
    )
    {
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_assetRepo = $assetRepo;
        $this->localeResolver = $localeResolver;
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
        $images = array();
        //gateways
        $images['ideal'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/ideal.png");
        $images['visa'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/visa.png");
        $images['dotpay'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/dotpay.png");
        $images['betaalnaontvangst'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/betaalnaontvangst.png");
        $images['einvoice'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/einvoice.png");
        $images['klarnainvoice'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/klarnainvoice.png");
        $images['bancontact'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/bancontact.png");
        $images['eps'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/eps.png");
        $images['ferbuy'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/ferbuy.png");
        $images['mastercard'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/mastercard.png");
        $images['mspbanktransfer'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/mspbanktransfer.png");
        $images['maestro'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/maestro.png");
        $images['paypalmsp'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/paypalmsp.png");
        $images['giropay'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/giropay.png");
        $images['sofort'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/sofort.png");
        $images['directdebit'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/directdebit.png");
        $images['americanexpress'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/americanexpress.png");
        $images['paysafecard'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/paysafecard.png");
        $images['trustpay'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/trustpay.png");
        $images['kbc'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/kbc.png");
        $images['alipay'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/alipay.png");
        $images['belfius'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/belfius.png");
        $images['ing'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/ing.png");

        //giftcards
        $images['webshopgiftcard'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/webshopgiftcard.png");
        $images['babygiftcard'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/babygiftcard.png");
        $images['boekenbon'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/boekenbon.png");
        $images['erotiekbon'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/erotiekbon.png");
        $images['parfumcadeaukaart'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/parfumcadeaukaart.png");
        $images['yourgift'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/yourgift.png");
        $images['wijncadeau'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/wijncadeau.png");
        $images['gezondheidsbon'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/gezondheidsbon.png");
        $images['fashioncheque'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/fashioncheque.png");
        $images['fashiongiftcard'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/fashiongiftcard.png");
        $images['podium'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/podium.png");
        $images['givacard'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/givacard.png");
        $images['vvvbon'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/vvvbon.png");
        $images['sportenfit'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/sportenfit.png");
        $images['goodcard'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/goodcard.png");
        $images['nationaletuinbon'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/nationaletuinbon.png");
        $images['nationaleverwencadeaubon'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/nationaleverwencadeaubon.png");
        $images['beautyandwellness'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/beautyandwellness.png");
        $images['fietsenbon'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/fietsenbon.png");
        $images['wellnessgiftcard'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/wellnessgiftcard.png");
        $images['winkelcheque'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/".strtolower($this->localeResolver->getLocale())."/winkelcheque.png");

        return $images;
    }
    
    public function getActiveMethod(){
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
                    'active_method'=> $this->getActiveMethod(),
                    'images' => $this->getImageURLs()
                ],
            ],
        ]);

        return $config;
    }

}
