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
 * @author      MultiSafepay <techsupport@multisafepay.com>
 * @copyright   Copyright (c) 2019 MultiSafepay, Inc. (https://www.multisafepay.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
 * ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace MultiSafepay\Connect\Test\Integration\Model;

use MultiSafepay\Connect\Model\Url;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Exception\ValidatorException;

/**
 * Class UrlTest
 */
class UrlTest extends TestCase
{
    /**
     * @var Url
     */
    protected $urlModelInstance;
    protected $objectManager;

    /**
     * Phpunit setup
     */
    protected function setUp(): void
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->urlModelInstance = $this->objectManager->get(Url::class);
    }

    /**
     * Test the default redirect url
     */
    public function testDefaultRedirectUrl(): void
    {
        $this->assertEquals(
            'http://localhost/index.php/multisafepay/connect/success',
            $this->urlModelInstance->getRedirectUrl()
        );
    }

    /**
     * Test the default cancel url
     */
    public function testDefaultCancelUrl(): void
    {
        $this->assertEquals(
            'http://localhost/index.php/multisafepay/connect/cancel',
            $this->urlModelInstance->getCancelUrl()
        );
    }

    /**
     * Test the default notification url
     */
    public function testDefaultNotificationUrl(): void
    {
        $this->assertEquals(
            'http://localhost/index.php/multisafepay/connect/notification?type=initial',
            $this->urlModelInstance->getNotificationUrl()
        );
    }

    /**
     * Build an url with only an endpoint.
     */
    public function testBuildUrlWithEndpointOnly(): void
    {
        $endpoint = 'multisafepay/connect/dummy';

        $result = $this->urlModelInstance->buildUrl($endpoint);
        $expected = 'http://localhost/index.php/multisafepay/connect/dummy';

        $this->assertEquals($expected, $result);
    }

    /**
     * Build an Url with Url parameters.
     */
    public function testBuildUrlWithUrlParameters(): void
    {
        $endpoint = 'multisafepay/connect/dummy';
        $params = ['foo' => 'bar'];

        $result = $this->urlModelInstance->buildUrl($endpoint, $params);
        $expected = 'http://localhost/index.php/multisafepay/connect/dummy?foo=bar';

        $this->assertEquals($expected, $result);
    }

    /**
     * Build an Url with an store id url and Parameters.
     */
    public function testBuildUrlWithUrlParametersAndStoreId(): void
    {
        $endpoint = 'multisafepay/connect/dummy';
        $params = ['foo' => 'bar'];
        $storeId = 1;

        $result = $this->urlModelInstance->buildUrl($endpoint, $params, $storeId);
        $expected = 'http://localhost/index.php/multisafepay/connect/dummy?foo=bar';

        $this->assertEquals($expected, $result);
    }

    /**
     * Build an Url with no Url parameters but with a Store id.
     */
    public function testBuildUrlWithStoreIdAndNoUrlParameters(): void
    {
        $endpoint = 'multisafepay/connect/dummy';
        $params = [];
        $storeId = 1;

        $result = $this->urlModelInstance->buildUrl($endpoint, $params, $storeId);
        $expected = 'http://localhost/index.php/multisafepay/connect/dummy';

        $this->assertEquals($expected, $result);
    }


    /**
     * Throw an Exception if Url is invalid
     */
    public function testBuildUrlWithInvalidUrl(): void
    {
        $endpoint = 'multisafepay/connect/ dummy';
        $params = [];
        $storeId = 1;

        $this->expectException(ValidatorException::class);
        $this->urlModelInstance->buildUrl($endpoint, $params, $storeId);
    }

    /**
     * Create a redirect url that is empty.
     *
     * @depends testDefaultNotificationUrl
     */
    public function testSetRedirectUrlWithEmptyUrl(): void
    {
        $this->urlModelInstance->setNotificationUrl('');
        $this->assertEquals('', $this->urlModelInstance->getNotificationUrl());
    }
}
