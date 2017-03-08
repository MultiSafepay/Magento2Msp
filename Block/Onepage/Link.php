<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MultiSafepay\Connect\Block\Onepage;

/**
 * One page checkout cart link
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Link extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    protected $_assetRepo;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $_checkoutHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param array $data
     */
    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, \Magento\Checkout\Model\Session $checkoutSession, \Magento\Checkout\Helper\Data $checkoutHelper, \Magento\Framework\View\Asset\Repository $assetRepo, array $data = []
    )
    {
        $this->_checkoutHelper = $checkoutHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_assetRepo = $assetRepo;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl('multisafepay/fastcheckout/redirect');
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        $quote = $this->_checkoutSession->getQuote();
        $allowed_currency = $this->_scopeConfig->getValue('fastcheckout/fastcheckout/allowed_currency', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $fco_active = $this->_scopeConfig->getValue('fastcheckout/fastcheckout/fastcheckout_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        //Check currency rescrictions
        $allowedCurrencies = explode(',', $allowed_currency);
        if (!in_array($quote->getQuoteCurrencyCode(), $allowedCurrencies)) {
            return true;
        }

        if (!$fco_active) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isPossibleOnepageCheckout()
    {
        return $this->_checkoutHelper->canOnepageCheckout();
    }

    public function getCheckoutImageUrl()
    {
        return $this->_assetRepo->getUrl("MultiSafepay_Connect::images/fastcheckout.png");
    }

}
