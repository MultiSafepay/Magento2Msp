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

declare(strict_types=1);

namespace MultiSafepay\Connect\Model\System\Message;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;

/**
 * Notifications class
 */
class PluginDeprecateNotification implements MessageInterface
{
    private const MESSAGE_IDENTITY = 'multisafepay_plugin_deprecate_notification';
    private const NEW_MAGENTO_PLUGIN_URL =
        'https://docs.multisafepay.com/integrations/ecommerce-integrations/magento2/';
    private const NEW_MAGENTO_PLUGIN_UPGRADE_FAQ_URL =
        'https://docs.multisafepay.com/integrations/ecommerce-integrations/magento2/faq/migrating-to-new-plugin/';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function getIdentity(): string
    {
        return hash("sha256", self::MESSAGE_IDENTITY);
    }

    /**
     * @return bool
     */
    public function isDisplayed(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        $messageDetails = '<strong style="font-size: 16px;">';
        $messageDetails .= __('This store is currently using the deprecated MultiSafepay plugin. ');
        $messageDetails .= '</strong><p style="margin-top: 10px;">';
        $messageDetails .= __(
            'Please upgrade to the new version of the <a href="%1" target="_blank">MultiSafepay Magento 2 plugin</a>.'
            . ' It brings code improvements, new features, unit/integration testing and it is built'
            . ' on top of the Magento payment provider gateway structure.',
            $this->urlBuilder->escape(self::NEW_MAGENTO_PLUGIN_URL)
        );
        $messageDetails .= '</p><p style="margin-top: 10px;">';
        $messageDetails .= __(
            'Before updating, please read this <a href="%1" target="_blank">FAQ article</a>'
            . ' about how you can migrate to the new plugin.',
            $this->urlBuilder->escape(self::NEW_MAGENTO_PLUGIN_UPGRADE_FAQ_URL)
        );
        $messageDetails .= '</p><p style="margin-top: 10px;">';
        $messageDetails .= __(
            ' If you have any questions regarding the plugin, feel free to contact our Integration Team at'
            . ' <a href="mailto:integration@multisafepay.com">integration@multisafepay.com</a>'
        );
        $messageDetails .= '</p>';

        return $messageDetails;
    }

    /**
     * @return int
     */
    public function getSeverity(): int
    {
        return self::SEVERITY_MAJOR;
    }
}
