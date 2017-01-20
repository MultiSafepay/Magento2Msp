<?php

namespace MultiSafepay\Connect\Model;

class ConnectConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
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
        $images['ideal'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/ideal.png");
        $images['visa'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/visa.png");
        $images['dotpay'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/dotpay.png");
        $images['betaalnaontvangst'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/betaalnaontvangst.png");
        $images['einvoice'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/einvoice.png");
        $images['klarnainvoice'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/klarnainvoice.png");
        $images['bancontact'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/bancontact.png");
        $images['eps'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/eps.png");
        $images['ferbuy'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/ferbuy.png");
        $images['mastercard'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/mastercard.png");
        $images['mspbanktransfer'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/mspbanktransfer.png");
        $images['maestro'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/maestro.png");
        $images['paypalmsp'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/paypalmsp.png");
        $images['giropay'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/giropay.png");
        $images['sofort'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/sofort.png");
        $images['directdebit'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/directdebit.png");
        $images['americanexpress'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/americanexpress.png");

        //giftcards
        $images['webshopgiftcard'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/webshopgiftcard.png");
        $images['babygiftcard'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/babygiftcard.png");
        $images['boekenbon'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/boekenbon.png");
        $images['erotiekbon'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/erotiekbon.png");
        $images['parfumcadeaukaart'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/parfumcadeaukaart.png");
        $images['yourgift'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/yourgift.png");
        $images['wijncadeau'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/wijncadeau.png");
        $images['gezondheidsbon'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/gezondheidsbon.png");
        $images['fashioncheque'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/fashioncheque.png");
        $images['fashiongiftcard'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/fashiongiftcard.png");
        $images['podium'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/podium.png");
        $images['givacard'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/givacard.png");
        $images['vvvbon'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/vvvbon.png");
        $images['sportenfit'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/sportenfit.png");
        $images['goodcard'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/goodcard.png");
        $images['nationaletuinbon'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/nationaletuinbon.png");
        $images['nationaleverwencadeaubon'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/nationaleverwencadeaubon.png");
        $images['beautyandwellness'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/beautyandwellness.png");
        $images['fietsenbon'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/fietsenbon.png");
        $images['wellnessgiftcard'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/wellnessgiftcard.png");
        $images['winkelcheque'] = $this->_assetRepo->getUrl("MultiSafepay_Connect::images/winkelcheque.png");

        return $images;
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
                    'images' => $this->getImageURLs()
                ],
            ],
        ]);

        return $config;
    }

}
