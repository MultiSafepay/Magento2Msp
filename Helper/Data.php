<?php

/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is provided with Magento in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the MultiSafepay plugin
 * to newer versions in the future. If you wish to customize the plugin for your
 * needs please document your changes and make backups before your update.
 *
 * @category    MultiSafepay
 * @package     Connect
 * @author      Ruud Jonk <techsupport@multisafepay.com>
 * @copyright   Copyright (c) 2015 MultiSafepay, Inc. (http://www.multisafepay.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
 * ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace MultiSafepay\Connect\Helper;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use MultiSafepay\Connect\Model\Api\MspClient;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection as orderStatusCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data
{

    const MSP_COMPLETED = "completed";
    const MSP_INIT = "initialized";
    const MSP_UNCLEARED = "uncleared";
    const MSP_VOID = "void";
    const MSP_DECLINED = "declined";
    const MSP_EXPIRED = "expired";
    const MSP_CANCELLED = "cancelled";
    const MSP_CHARGEBACK = "chargedback";
    const MSP_REFUNDED = "refunded";
    const MSP_PARTIAL_REFUNDED = "partial_refunded";

    public $giftcards = array(
        'webshopgiftcard',
        'babygiftcard',
        'boekenbon',
        'erotiekbon',
        'parfumcadeaukaart',
        'yourgift',
        'wijncadeau',
        'gezondheidsbon',
        'fashioncheque',
        'fashiongiftcard',
        'podium',
        'vvvbon',
        'sportenfit',
        'goodcard',
        'nationaletuinbon',
        'nationaleverwencadeaubon',
        'beautyandwellness',
        'fietsenbon',
        'wellnessgiftcard',
        'winkelcheque',
        'givacard'
    );
    public $gateways = array(
        'ideal',
        'dotpay',
        'betaalnaontvangst',
        'einvoice',
        'klarnainvoice',
        'afterpaymsp',
        'bancontact',
        'visa',
        'betaalplan',
        'eps',
        'ferbuy',
        'mastercard',
        'mspbanktransfer',
        'maestro',
        'paypalmsp',
        'giropay',
        'sofort',
        'directdebit',
        'americanexpress',
        'creditcard',
        'paysafecard',
        'trustpay',
        'kbc',
        'alipay',
        'belfius',
        'ing',
        'idealqr',
        'trustly',
    );
    //MultiSafepay_gateways->Magento_codes
    public $methodMap = array(
        'ALIPAY' => 'alipay',
        'AMEX' => 'americanexpress',
        'BANKTRANS' => 'mspbanktransfer',
        'BELFIUS' => 'belfius',
        'DIRDEB' => 'directdebit',
        'DIRECTBANK' => 'sofort',
        'DOTPAY' => 'dotpay',
        'EINVOICE' => 'einvoice',
        'EPS' => 'eps',
        'FERBUY' => 'ferbuy',
        'GIROPAY' => 'giropay',
        'IDEAL' => 'ideal',
        'INGHOME' => 'ing',
        'KBC' => 'kbc',
        'KLARNA' => 'klarnainvoice',
        'MAESTRO' => 'maestro',
        'MASTERCARD' => 'mastercard',
        'MISTERCASH' => 'bancontact',
        'PAYAFTER' => 'betaalnaontvangst',
        'PAYPAL' => 'paypalmsp',
        'PSAFECARD' => 'paysafecard',
        'TRUSTPAY' => 'trustpay',
        'VISA' => 'visa',
        'SANTANDER' => 'betaalplan',
        'AFTERPAY' => 'afterpaymsp',
        'IDEALQR' => 'idealqr',
        'TRUSTLY' => 'trustly',

        'BABYGIFTCARD' => 'babygiftcard',
        'BEAUTYANDWELLNESS' => 'beautyandwellness',
        'BOEKENBON' => 'boekenbon',
        'EROTIEKBON' => 'erotiekbon',
        'FASHIONCHEQUE' => 'fashioncheque',
        'FASHIONGIFTCARD' => 'fashiongiftcard',
        'FIETSENBON' => 'fietsenbon',
        'GEZONDHEIDSBON' => 'gezondheidsbon',
        'GIVACARD' => 'givacard',
        'GOODCARD' =>'goodcard',
        'NATIONALETUINBON' => 'nationaletuinbon',
        'NATIONALEVERWENCADEAUBON' => 'nationaleverwencadeaubon',
        'PARFUMCADEAUKAART' => 'parfumcadeaukaart',
        'PODIUM' => 'podium',
        'SPORTENFIT'=>'sportenfit',
        'VVVBON' =>'vvvbon',
        'WEBSHOPGIFTCARD' => 'webshopgiftcard',
        'WELLNESSGIFTCARD' => 'wellnessgiftcard',
        'WIJNCADEAU' => 'wijncadeau',
        'WINKELCHEQUE' => 'winkelcheque',
        'YOURGIFT' => 'yourgift',
    );

    /**
     * File extension lock
     */
    const LOCK_EXTENSION = '.lock';

    /**
     * Max execution (locking) time for process (in seconds)
     */
    const MAX_LOCK_TIME = 20;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $lockFilePath;

    /**
     * @var WriteInterface
     */
    private $tmpDirectory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManagerInterface;

    /**
     * @var orderStatusCollection;
     */
    protected $_orderStatusCollection;
    protected $_ScopeConfigInterface;

    public function __construct(
        StoreManagerInterface $storeManagerInterface,
        orderStatusCollection $orderStatusCollection,
        Filesystem $filesystem,
        ScopeConfigInterface $scopeConfigInterface
    ) {
        $this->_storeManagerInterface = $storeManagerInterface;
        $this->filesystem = $filesystem;
        $this->_orderStatusCollection = $orderStatusCollection;
        $this->_scopeConfigInterface = $scopeConfigInterface;

        $this->tmpDirectory = $this->filesystem->getDirectoryWrite(
            DirectoryList::VAR_DIR
        );
    }

    /**
     * @inheritdoc
     */
    public function lockProcess($lockName)
    {
        $this->tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->lockFilePath = $this->getFilePath($lockName);
        while ($this->isProcessLocked()) {
            usleep(1000);
        }
        $this->tmpDirectory->writeFile($this->lockFilePath, time());
    }

    /**
     * @inheritdoc
     * @throws FileSystemException
     */
    public function unlockProcess($lockName)
    {
        $this->lockFilePath = $this->getFilePath($lockName);
        $this->tmpDirectory->delete($this->lockFilePath);
    }

    /**
     * Check whether generation process has already locked
     *
     * @return bool
     */
    private function isProcessLocked()
    {
        if ($this->tmpDirectory->isExist($this->lockFilePath)) {
            try {
                $lockTime = (int) $this->tmpDirectory->readFile($this->lockFilePath);
                if ((time() - $lockTime) >= self::MAX_LOCK_TIME) {
                    $this->tmpDirectory->delete($this->lockFilePath);
                    return false;
                }
            } catch (FileSystemException $e) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Get name of lock file
     *
     * @param string $name
     * @return string
     */
    private function getFilePath($name)
    {
        return DirectoryList::TMP . DIRECTORY_SEPARATOR . $name . self::LOCK_EXTENSION;
    }

    public function getAmountInCents($order, $use_base_currency)
    {
        if ($use_base_currency) {
            return round($order->getBaseGrandTotal() * 100);
        } else {
            return round($order->getGrandTotal() * 100);
        }
    }

    public function getCurrencyCode($order, $use_base_currency)
    {
        if ($use_base_currency) {
            return $order->getBaseCurrencyCode();
        } else {
            return $order->getOrderCurrencyCode();
        }
    }

    public function getAllMethods()
    {
        $methods = array_merge($this->gateways, $this->giftcards);

        $all_methods = array();

        foreach ($methods as $key => $method) {
            $all_methods[$method] = $method;
        }

        return $all_methods;
    }

    public function getPaymentType($code)
    {
        if (in_array($code, $this->gateways)) {
            return 'gateways';
        } elseif (in_array($code, $this->giftcards)) {
            return 'giftcards';
        }
    }

    /**
     * Check if transaction was a fastcheckout transaction
     *
     * @param array transaction_details
     * @return boolean
     */
    public function isFastcheckoutTransaction($transaction_details)
    {
        if (isset($transaction_details['Fastcheckout'])) {
            if ($transaction_details['Fastcheckout'] == "YES") {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Returns payment code based on MultiSafepay gateway
     *
     * @param string gateway
     * @return string
     */
    public function getPaymentCode($gateway)
    {
        if (isset($this->methodMap[$gateway])) {
            return $this->methodMap[$gateway];
        } else {
            return null;
        }
    }

    /**
     * Returns assigned state for status
     *
     * @param string status
     * @return string
     */
    public function getAssignedState($status)
    {
        $item = $this->_orderStatusCollection
            ->joinStates()
            ->addFieldToFilter('main_table.status', $status)
            ->getFirstItem();
        return $item->getState();
    }


    public function getStoreId()
    {
        $storeManager = $this->_storeManagerInterface;

        return $storeManager->getStore()->getId();
    }

    public function getConfig()
    {

        return $this->_scopeConfigInterface;
    }

    public function initializeClient($environment, $order, MspClient $mspClient)
    {
        if ($environment == true) {
            $mspClient->setApiKey($this->getConfigData(
                'test_api_key',
                $order->getPayment()->getMethodInstance()->getCode(),
                $order->getStoreId()
            ));
            $mspClient->setApiUrl('https://testapi.multisafepay.com/v1/json/');
        } else {
            $mspClient->setApiKey($this->getConfigData(
                'live_api_key',
                $order->getPayment()->getMethodInstance()->getCode(),
                $order->getStoreId()

            ));
            $mspClient->setApiUrl('https://api.multisafepay.com/v1/json/');
        }

    }

    public function getMainConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $this->getStoreId();
        }


        $path = "multisafepay/connect/{$field}";
        return $this->getConfig()->getValue($path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }


    public function getConfigData($field, $code, $storeId = null){

        if (null === $storeId) {
            $storeId = $this->getStoreId();
        }
        $mspType = $this->getPaymentType($code);


        $path = $mspType . '/' . $code . '/' . $field;

        if ($field == "test_api_key" || $field == "live_api_key") {
            return $this->getMainConfigData($field, $storeId);
        }
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getStoreName()
    {
        return $this->_storeManagerInterface->getStore()->getFrontendName();
    }

    public function isMspGateway($gateway)
    {
        if(in_array($gateway, $this->gateways))
        {
            return true;
        }
        return false;
    }
    public function isMspGiftcard($giftcard)
    {
        if(in_array($giftcard, $this->giftcards))
        {
            return true;
        }
        return false;
    }
}
