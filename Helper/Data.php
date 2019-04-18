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
 * @copyright   Copyright (c) 2018 MultiSafepay, Inc. (https://www.multisafepay.com)
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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Math\Random;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection as orderStatusCollection;
use Magento\Store\Model\StoreManagerInterface;
use MultiSafepay\Connect\Model\Api\MspClient;
use MultiSafepay\Connect\Model\MultisafepayTokenizationFactory;

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

    public $gateways = array(
        'afterpaymsp' => array('code' => 'AFTERPAY', 'name' => 'AfterPay', 'type' => 'gateways'),
        'alipay' => array('code' => 'ALIPAY', 'name' => 'Alipay', 'type' => 'gateways'),
        'americanexpress' => array('code' => 'AMEX', 'name' => 'American Express', 'type' => 'gateways'),
        'bancontact' => array('code' => 'MISTERCASH', 'name' => 'Bancontact', 'type' => 'gateways'),
        'belfius' => array('code' => 'BELFIUS', 'name' => 'Belfius', 'type' => 'gateways'),
        'betaalnaontvangst'  => array('code' => 'PAYAFTER', 'name' => 'Pay After Delivery', 'type' => 'gateways'),
        'betaalplan'  => array('code' => 'SANTANDER', 'name' => 'Santander Betaalplan', 'type' => 'gateways'),
        'creditcard'  => array('code' => '', 'name' => 'Creditcard', 'type' => 'gateways'),
        'directdebit' => array('code' => 'DIRDEB', 'name' => 'Direct Debit', 'type' => 'gateways'),
        'dotpay'  => array('code' => 'DOTPAY', 'name' => 'Dotpay', 'type' => 'gateways'),
        'einvoice' => array('code' => 'EINVOICE', 'name' => 'E-Invoice', 'type' => 'gateways'),
        'eps'  => array('code' => 'EPS', 'name' => 'EPS', 'type' => 'gateways'),
        'ferbuy' => array('code' => 'AMEX', 'name' => 'Ferbuy', 'type' => 'gateways'),
        'giropay' => array('code' => 'GIROPAY', 'name' => 'GiroPay', 'type' => 'gateways'),
        'ideal'  => array('code' => 'IDEAL', 'name' => 'iDEAL', 'type' => 'gateways'),
        'idealqr' => array('code' => 'IDEALQR', 'name' => 'iDEAL QR', 'type' => 'gateways'),
        'ing' => array('code' => 'INGHOME', 'name' => 'ING Home\'Pay', 'type' => 'gateways'),
        'kbc' => array('code' => 'KBC', 'name' => 'KBC', 'type' => 'gateways'),
        'klarnainvoice' => array('code' => 'KLARNA', 'name' => 'Klarna', 'type' => 'gateways'),
        'maestro' => array('code' => 'MAESTRO', 'name' => 'Maestro', 'type' => 'gateways'),
        'mastercard' => array('code' => 'MASTERCARD', 'name' => 'Mastercard', 'type' => 'gateways'),
        'mspbanktransfer' => array('code' => 'BANKTRANS', 'name' => 'Banktransfer', 'type' => 'gateways'),
        'multisafepay' => array('code' => '', 'name' => 'MultiSafepay', 'type' => 'gateways'),
        'paypalmsp' => array('code' => 'PAYPAL', 'name' => 'PayPal', 'type' => 'gateways'),
        'paysafecard' => array('code' => 'PSAFECARD', 'name' => 'PaySafeCard', 'type' => 'gateways'),
        'sofort' => array('code' => 'DIRECTBANK', 'name' => 'SOFORT Banking', 'type' => 'gateways'),
        'trustly' => array('code' => 'TRUSTLY', 'name' => 'Trustly', 'type' => 'gateways'),
        'trustpay' => array('code' => 'TRUSTPAY', 'name' => 'Trustpay', 'type' => 'gateways'),
        'visa' => array('code' => 'VISA', 'name' => 'Visa', 'type' => 'gateways'),

        'babygiftcard' => array('code' => 'BABYGIFTCARD', 'name' => 'Babygiftcard', 'type' => 'giftcards'),
        'beautyandwellness' => array('code' => 'BEAUTYANDWELLNESS', 'name' => 'Beauty and wellness', 'type' => 'giftcards'),
        'boekenbon' => array('code' => 'BOEKENBON', 'name' => 'Boekenbon', 'type' => 'giftcards'),
        'erotiekbon' => array('code' => 'EROTIEKBON', 'name' => 'Erotiekbon', 'type' => 'giftcards'),
        'fashioncheque' => array('code' => 'FASHIONCHEQUE', 'name' => 'Fashioncheque', 'type' => 'giftcards'),
        'fashiongiftcard' => array('code' => 'FASHIONGIFTCARD', 'name' => 'Fashiongiftcard', 'type' => 'giftcards'),
        'fietsenbon' => array('code' => 'FIETSENBON', 'name' => 'Fietsenbon', 'type' => 'giftcards'),
        'gezondheidsbon' => array('code' => 'GEZONDHEIDSBON', 'name' => 'Gezondheidsbon', 'type' => 'giftcards'),
        'givacard' => array('code' => 'GIVACARD', 'name' => 'Givacard', 'type' => 'giftcards'),
        'goodcard' => array('code' => 'GOODCARD', 'name' => 'Goodcard', 'type' => 'giftcards'),
        'nationaletuinbon' => array('code' => 'NATIONALETUINBON', 'name' => 'Nationale tuinbon', 'type' => 'giftcards'),
        'nationaleverwencadeaubon' => array('code' => 'NATIONALEVERWENCADEAUBON', 'name' => 'Nationale verwencadeaubon', 'type' => 'giftcards'),
        'parfumcadeaukaart' => array('code' => 'PARFUMCADEAUKAART', 'name' => 'Parfumcadeaukaart', 'type' => 'giftcards'),
        'podiumcadeaukaart' => array('code' => 'PODIUM', 'name' => 'Podium', 'type' => 'giftcards'),
        'sportenfit' => array('code' => 'SPORTENFIT', 'name' => 'Sportenfit', 'type' => 'giftcards'),
        'vvvcadeaukaart' => array('code' => 'VVVGIFTCRD', 'name' => 'VVV Cadeaukaart', 'type' => 'giftcards'),
        'webshopgiftcard' => array('code' => 'WEBSHOPGIFTCARD', 'name' => 'Webshop Giftcard', 'type' => 'giftcards'),
        'wellnessgiftcard' => array('code' => 'WELLNESSGIFTCARD', 'name' => 'Wellness Giftcards', 'type' => 'giftcards'),
        'wijncadeau' => array('code' => 'WIJNCADEAU', 'name' => 'Wijn Cadeau', 'type' => 'giftcards'),
        'winkelcheque' => array('code' => 'WINKELCHEQUE', 'name' => 'Winkel Cheque', 'type' => 'giftcards'),
        'yourgift' => array('code' => 'YOURGIFT', 'name' => 'YourGift', 'type' => 'giftcards'),
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
    protected $_random;
    protected $_encryptor;

    public function __construct(
        StoreManagerInterface $storeManagerInterface,
        orderStatusCollection $orderStatusCollection,
        Filesystem $filesystem,
        ScopeConfigInterface $scopeConfigInterface,
        Random $random,
        MultisafepayTokenizationFactory $multisafepayTokenizationFactory,
        EncryptorInterface $encryptor



    ) {
        $this->_random = $random;
        $this->_storeManagerInterface = $storeManagerInterface;
        $this->filesystem = $filesystem;
        $this->_orderStatusCollection = $orderStatusCollection;
        $this->_scopeConfigInterface = $scopeConfigInterface;

        $this->tmpDirectory = $this->filesystem->getDirectoryWrite(
            DirectoryList::VAR_DIR
        );

        $this->_encryptor = $encryptor;
        $this->_mspToken = $multisafepayTokenizationFactory;
    }

    /**
     * @inheritdoc
     */
    public function lockProcess($lockName)
    {
        $this->tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
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
        return $name . self::LOCK_EXTENSION;
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
        $paymentMethods = array();

        foreach ($this->gateways as $key => $gateway){
            $paymentMethods[$key] = $gateway['name'];
        }

        return $paymentMethods;
    }

    public function getPaymentType($code)
    {
        return (isset($this->gateways[$code])) ? $this->gateways[$code]['type'] : null;
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
        return (isset($this->gateways[$gateway])) ? $this->gateways[$gateway]['code'] : null;
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
        return $this->getConfig()->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }


    public function getConfigData($field, $code, $storeId = null)
    {

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
        if (isset($this->gateways[$gateway])
            && $this->gateways[$gateway]['type'] === 'gateways'
        ) {
            return true;
        }
        return false;
    }

    public function isMspGiftcard($giftcard)
    {
        if (isset($this->gateways[$giftcard])
            && $this->gateways[$giftcard]['type'] === 'giftcards'
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param         $customerId
     * @param boolean $showNull
     *
     * @return integer
     */
    public function getRecurringIdsByCustomerId($customerId, $showNull = false)
    {
        return $this->_mspToken->create()->getIdsByCustomerId(
            $customerId, $showNull
        );
    }

    /**
     * @param $hash
     *
     * @return integer
     */
    public function getRecurringIdByHash($hash)
    {
        return $this->_mspToken->create()->getIdByHash(
            $hash
        );
    }

    /**
     * @param integer $orderId
     *
     * @return integer
     */
    public function getRecurringIdByOrderId($orderId){
        return $this->_mspToken->create()->getIdByOrderId($orderId);
    }

    /**
     * @param array $array
     *
     * @return array
     */
    public function hideRecurringExpiredIds($array)
    {
        return $this->_mspToken->create()->hideRecurringExpiredIds(
            $array
        );
    }

    public function isEnabled($configData)
    {
        return boolval($this->getMainConfigData($configData));
    }

    /**
     * @return string
     */
    public function getUniqueHash()
    {
        return $this->_random->getUniqueHash();
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function encrypt($string)
    {
        return $this->_encryptor->encrypt($string);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function decrypt($string){
        return $this->_encryptor->decrypt($string);
    }

    /**
     * @param string $orderIncrementId
     * @param string $hash
     *
     * @return bool
     * @throws \Exception
     */
    public function validateOrderHash($orderIncrementId ,$hash)
    {
        return hash_equals($this->encryptOrder($orderIncrementId), $hash);
    }

    /**
     * @param string $orderIncrementId
     *
     * @return string
     * @throws \Exception
     */
    public function encryptOrder($orderIncrementId)
    {
        $api_key = $this->getMainConfigData('live_api_key');

        if ($this->isTestEnvironment()) {
            $api_key = $this->getMainConfigData('test_api_key');
        }

        if (empty($api_key)) {
            throw new \Exception('Please configure your MultiSafepay API Key.');
        }

        return hash_hmac('sha512', $orderIncrementId, $api_key);
    }

    /**
     * @return bool
     */
    public function isTestEnvironment()
    {
        return $this->getMainConfigData('msp_env');
    }


}
