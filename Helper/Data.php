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
 * @author      MultiSafepay <integration@multisafepay.com>
 * @copyright   Copyright (c) MultiSafepay, Inc. (https://www.multisafepay.com)
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

use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Math\Random;
use Magento\Sales\Api\Data\OrderInterface;
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

    public $gateways = [
        'afterpaymsp' => ['code' => 'AFTERPAY', 'name' => 'AfterPay', 'type' => 'gateways'],
        'alipay' => ['code' => 'ALIPAY', 'name' => 'Alipay', 'type' => 'gateways'],
        'americanexpress' => ['code' => 'AMEX', 'name' => 'American Express', 'type' => 'gateways'],
        'applepay' => ['code' => 'APPLEPAY', 'name' => 'Apple Pay', 'type' => 'gateways'],
        'bancontact' => ['code' => 'MISTERCASH', 'name' => 'Bancontact', 'type' => 'gateways'],
        'belfius' => ['code' => 'BELFIUS', 'name' => 'Belfius', 'type' => 'gateways'],
        'betaalnaontvangst'  => ['code' => 'PAYAFTER', 'name' => 'Pay After Delivery', 'type' => 'gateways'],
        'betaalplan'  => ['code' => 'SANTANDER', 'name' => 'Betaalplan', 'type' => 'gateways'],
        'creditcard'  => ['code' => 'CREDITCARD', 'name' => 'Credit card', 'type' => 'gateways'],
        'directdebit' => ['code' => 'DIRDEB', 'name' => 'Direct Debit', 'type' => 'gateways'],
        'directbanktransfer' => ['code' => 'DBRTP', 'name' => 'Direct Bank Transfer', 'type' => 'gateways'],
        'dotpay'  => ['code' => 'DOTPAY', 'name' => 'Dotpay', 'type' => 'gateways'],
        'einvoice' => ['code' => 'EINVOICE', 'name' => 'E-Invoicing', 'type' => 'gateways'],
        'eps'  => ['code' => 'EPS', 'name' => 'EPS', 'type' => 'gateways'],
        'giropay' => ['code' => 'GIROPAY', 'name' => 'GiroPay', 'type' => 'gateways'],
        'ideal'  => ['code' => 'IDEAL', 'name' => 'iDEAL', 'type' => 'gateways'],
        'idealqr' => ['code' => 'IDEALQR', 'name' => 'iDEAL QR', 'type' => 'gateways'],
        'ing' => ['code' => 'INGHOME', 'name' => 'ING Home\'Pay', 'type' => 'gateways'],
        'kbc' => ['code' => 'KBC', 'name' => 'KBC', 'type' => 'gateways'],
        'klarnainvoice' => ['code' => 'KLARNA', 'name' => 'Klarna', 'type' => 'gateways'],
        'maestro' => ['code' => 'MAESTRO', 'name' => 'Maestro', 'type' => 'gateways'],
        'mastercard' => ['code' => 'MASTERCARD', 'name' => 'Mastercard', 'type' => 'gateways'],
        'mspbanktransfer' => ['code' => 'BANKTRANS', 'name' => 'Bank transfer', 'type' => 'gateways'],
        'multisafepay' => ['code' => '', 'name' => 'MultiSafepay', 'type' => 'gateways'],
        'paypalmsp' => ['code' => 'PAYPAL', 'name' => 'PayPal', 'type' => 'gateways'],
        'paysafecard' => ['code' => 'PSAFECARD', 'name' => 'Paysafecard', 'type' => 'gateways'],
        'sofort' => ['code' => 'DIRECTBANK', 'name' => 'SOFORT Banking', 'type' => 'gateways'],
        'trustly' => ['code' => 'TRUSTLY', 'name' => 'Trustly', 'type' => 'gateways'],
        'trustpay' => ['code' => 'TRUSTPAY', 'name' => 'Trustpay', 'type' => 'gateways'],
        'visa' => ['code' => 'VISA', 'name' => 'Visa', 'type' => 'gateways'],

        'babygiftcard' => ['code' => 'BABYGIFTCARD', 'name' => 'Babygiftcard', 'type' => 'giftcards'],
        'beautyandwellness' => ['code' => 'BEAUTYANDWELLNESS', 'name' => 'Beauty and wellness', 'type' => 'giftcards'],
        'boekenbon' => ['code' => 'BOEKENBON', 'name' => 'Boekenbon', 'type' => 'giftcards'],
        'erotiekbon' => ['code' => 'EROTIEKBON', 'name' => 'Erotiekbon', 'type' => 'giftcards'],
        'fashioncheque' => ['code' => 'FASHIONCHEQUE', 'name' => 'Fashioncheque', 'type' => 'giftcards'],
        'fashiongiftcard' => ['code' => 'FASHIONGIFTCARD', 'name' => 'Fashiongiftcard', 'type' => 'giftcards'],
        'fietsenbon' => ['code' => 'FIETSENBON', 'name' => 'Fietsenbon', 'type' => 'giftcards'],
        'gezondheidsbon' => ['code' => 'GEZONDHEIDSBON', 'name' => 'Gezondheidsbon', 'type' => 'giftcards'],
        'givacard' => ['code' => 'GIVACARD', 'name' => 'Givacard', 'type' => 'giftcards'],
        'goodcard' => ['code' => 'GOODCARD', 'name' => 'Goodcard', 'type' => 'giftcards'],
        'nationaletuinbon' => ['code' => 'NATIONALETUINBON', 'name' => 'Nationale tuinbon', 'type' => 'giftcards'],
        'nationaleverwencadeaubon' => ['code' => 'NATIONALEVERWENCADEAUBON', 'name' => 'Nationale verwencadeaubon', 'type' => 'giftcards'],
        'parfumcadeaukaart' => ['code' => 'PARFUMCADEAUKAART', 'name' => 'Parfumcadeaukaart', 'type' => 'giftcards'],
        'podiumcadeaukaart' => ['code' => 'PODIUM', 'name' => 'Podium', 'type' => 'giftcards'],
        'sportenfit' => ['code' => 'SPORTENFIT', 'name' => 'Sportenfit', 'type' => 'giftcards'],
        'vvvbon' => ['code' => 'VVVGIFTCRD', 'name' => 'VVV Cadeaukaart', 'type' => 'giftcards'],
        'webshopgiftcard' => ['code' => 'WEBSHOPGIFTCARD', 'name' => 'Webshop Giftcard', 'type' => 'giftcards'],
        'wellnessgiftcard' => ['code' => 'WELLNESSGIFTCARD', 'name' => 'Wellness Giftcards', 'type' => 'giftcards'],
        'wijncadeau' => ['code' => 'WIJNCADEAU', 'name' => 'Wijn Cadeau', 'type' => 'giftcards'],
        'winkelcheque' => ['code' => 'WINKELCHEQUE', 'name' => 'Winkel Cheque', 'type' => 'giftcards'],
        'yourgift' => ['code' => 'YOURGIFT', 'name' => 'YourGift', 'type' => 'giftcards'],
    ];

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
    protected $_currencyFactory;

    public function __construct(
        StoreManagerInterface $storeManagerInterface,
        orderStatusCollection $orderStatusCollection,
        Filesystem $filesystem,
        ScopeConfigInterface $scopeConfigInterface,
        Random $random,
        MultisafepayTokenizationFactory $multisafepayTokenizationFactory,
        EncryptorInterface $encryptor,
        CurrencyFactory $currencyFactory
    ) {
        $this->_random = $random;
        $this->_storeManagerInterface = $storeManagerInterface;
        $this->filesystem = $filesystem;
        $this->_orderStatusCollection = $orderStatusCollection;
        $this->_scopeConfigInterface = $scopeConfigInterface;
        $this->_currencyFactory = $currencyFactory;

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
        $paymentMethods = [];

        foreach ($this->gateways as $key => $gateway) {
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
            $customerId,
            $showNull
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
    public function getRecurringIdByOrderId($orderId)
    {
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
    public function decrypt($string)
    {
        return $this->_encryptor->decrypt($string);
    }

    /**
     * @param string $orderIncrementId
     * @param string $hash
     *
     * @return bool
     * @throws \Exception
     */
    public function validateOrderHash($orderIncrementId, $hash)
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

    /**
     * You can use this method to communicate the MultiSafepay status to other systems by using a plugin
     *
     * @param OrderInterface $order
     * @param string $status
     * @return array
     */
    public function setMultisafepayStatus(OrderInterface $order, string $status)
    {
        $formatStatus = ucfirst($status);
        $order->setMultisafepayStatus($formatStatus);
        return [
            'orderId' => $order->getEntityId(),
            'mspStatus' => $formatStatus
        ];
    }

    /**
     * @param array $paymentMethods
     *
     * @return string
     */
    public function createMultiPaymentMethodsLine($paymentMethods = [])
    {
        if (empty($paymentMethods)) {
            return "";
        }
        $translation = __('Payment methods specification');
        $lineHeader = "<b>{$translation}:</b><br />";

        foreach ($paymentMethods as $paymentMethod) {
            $mspTotal = $this->_currencyFactory->create()->load(
                $paymentMethod->currency
            )->formatTxt((float)$paymentMethod->amount / 100);
            $line[] = "{$paymentMethod->type} ({$mspTotal})";
        }
        return $lineHeader . implode(' / ', $line);
    }

    /**
     * @return bool|mixed
     */
    public function getIssuers()
    {
        $mspClient = new MspClient();
        $environment = $this->getMainConfigData('msp_env');
        $apiKey = null;
        if ($environment) {
            $mspClient->setApiKey($this->getMainConfigData('test_api_key'));
            $apiKey = $this->getMainConfigData('test_api_key');
            $mspClient->setApiUrl('https://testapi.multisafepay.com/v1/json/');
        } else {
            $mspClient->setApiKey($this->getMainConfigData('live_api_key'));
            $apiKey = $this->getMainConfigData('live_api_key');
            $mspClient->setApiUrl('https://api.multisafepay.com/v1/json/');
        }
        if (empty($apiKey)) {
            return false;
        }
        try {
            $issuers = $mspClient->issuers->get();
        } catch (\Exception $e) {
            return false;
        }
        return $issuers;
    }
}
