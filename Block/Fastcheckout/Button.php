<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MultiSafepay\Connect\Block\Fastcheckout;

use Magento\Checkout\Model\Session;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Button
 */
class Button extends Template implements ShortcutInterface
{

    const ALIAS_ELEMENT_INDEX = 'fco_alias';
    const BUTTON_ELEMENT_INDEX = 'fco_button_id';

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var Session
     */
    private $checkoutSession;
    protected $_assetRepo;
    protected $_scopeConfig;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ResolverInterface $localeResolver
     * @param Session $checkoutSession
     * @param array $data
     */
    public function __construct(
    Context $context, ResolverInterface $localeResolver, Session $checkoutSession, array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->localeResolver = $localeResolver;
        $this->_assetRepo = $context->getAssetRepository();
        $this->checkoutSession = $checkoutSession;
        $this->_scopeConfig = $context->getScopeConfig();
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if ($this->isActive()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl('multisafepay/fastcheckout/redirect');
    }

    public function getCheckoutImageUrl()
    {
        return $this->_assetRepo->getUrl("MultiSafepay_Connect::images/" . strtolower($this->localeResolver->getLocale()) . "/fastcheckout.png");
    }

    /**
     * @inheritdoc
     */
    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    /**
     * @return string
     */
    public function getContainerId()
    {
        return $this->getData(self::BUTTON_ELEMENT_INDEX);
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return strtolower($this->localeResolver->getLocale());
    }

    /**
     * @return bool
     */
    public function isActive()
    {
	    $quote = $this->checkoutSession->getQuote();
	    if($quote->getQuoteCurrencyCode() != "EUR"){
		    return false;
	    }
	    
        return $this->_scopeConfig->getValue('fastcheckout/fastcheckout/fastcheckout_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}
