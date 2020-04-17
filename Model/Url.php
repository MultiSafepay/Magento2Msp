<?php
/**
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

namespace MultiSafepay\Connect\Model;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Phrase;

/**
 * Class Url
 */
class Url
{

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    public $cancelUrl;

    /**
     * @var string
     */
    public $notificationUrl;

    /**
     * @var string
     */
    public $redirectUrl;

    /**
     * Url constructor.
     * @param UrlInterface $urlInterface
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UrlInterface $urlInterface,
        StoreManagerInterface $storeManager
    ) {
        $this->urlBuilder = $urlInterface;
        $this->storeManager = $storeManager;
    }


    /**
     * @param string|null $cancelUrl
     * @param array $params
     * @param int|null $storeId
     * @return string
     */
    public function getCancelUrl(string $cancelUrl = null, array $params = [], int $storeId = null): string
    {
        if ($cancelUrl !== null) {
            $this->setCancelUrl($cancelUrl, $params, $storeId);
        } elseif ($this->cancelUrl === null) {
            $this->setCancelUrl('multisafepay/connect/cancel');
        }
        return $this->cancelUrl;
    }

    /**
     * @param string $cancelUrl
     * @param array $params
     * @param int|null $storeId
     * @return Url
     */
    public function setCancelUrl(string $cancelUrl, array $params = [], int $storeId = null): Url
    {
        if (trim($cancelUrl) === '') {
            $this->cancelUrl = '';
            return $this;
        }
        $this->cancelUrl = $this->buildUrl($cancelUrl, $params, $storeId);
        return $this;
    }

    /**
     * @param string|null $notificationUrl
     * @param array $params
     * @param int|null $storeId
     * @return string
     */
    public function getNotificationUrl(string $notificationUrl = null, array $params = [], int $storeId = null): string
    {
        if ($notificationUrl !== null) {
            $this->setNotificationUrl($notificationUrl, $params, $storeId);
        } elseif ($this->notificationUrl === null) {
            $this->setNotificationUrl('multisafepay/connect/notification', ['type' => 'initial']);
        }
        return $this->notificationUrl;
    }

    /**
     * @param string $notificationUrl
     * @param array $params
     * @param int|null $storeId
     * @return Url
     */
    public function setNotificationUrl(string $notificationUrl, array $params = [], int $storeId = null): Url
    {
        if (trim($notificationUrl) === '') {
            $this->notificationUrl = '';
            return $this;
        }
        $this->notificationUrl = $this->buildUrl($notificationUrl, $params, $storeId);
        return $this;
    }

    /**
     * @param string|null $redirectUrl
     * @param array $params
     * @param int|null $storeId
     * @return string
     */
    public function getRedirectUrl(string $redirectUrl = null, array $params = [], int $storeId = null): string
    {
        if ($redirectUrl !== null) {
            $this->setRedirectUrl($redirectUrl, $params, $storeId);
        } elseif ($this->redirectUrl === null) {
            $this->setRedirectUrl('multisafepay/connect/success');
        }
        return $this->redirectUrl;
    }

    /**
     * @param string $redirectUrl
     * @param array $params
     * @param int|null $storeId
     * @return Url
     */
    public function setRedirectUrl(string $redirectUrl, array $params = [], int $storeId = null): Url
    {
        if (trim($redirectUrl) === '') {
            $this->redirectUrl = '';
            return $this;
        }
        $this->redirectUrl = $this->buildUrl($redirectUrl, $params, $storeId);
        return $this;
    }

    /**
     * @param string $endpoint
     * @param array $params
     * @param int|null $storeId
     * @return string
     * @throws ValidatorException
     */
    public function buildUrl(string $endpoint, array $params = [], int $storeId = null): string
    {
        $url = $this->urlBuilder->getUrl(
            $endpoint,
            ['_nosid' => true, '_query' => $params]
        );

        if ($storeId) {
            $storeUrl = $this->storeManager->getStore($storeId)->getBaseUrl();
            $queryString = http_build_query($params);

            $url = $storeUrl . $endpoint;
            if ($queryString) {
                $url .= '?' . $queryString;
            }
        }

        if (!$this->isValidUrl($url)) {
            throw new ValidatorException(new Phrase('Url is not valid'));
        }

        $url = str_replace('/?', '?', $url);
        $url = rtrim($url, '/');

        return $url;
    }

    /**
     * Check if $url is valid
     *
     * @param string $url
     * @return bool
     */
    private function isValidUrl(string $url): bool
    {
        return (bool)filter_var($url, FILTER_VALIDATE_URL);
    }
}
