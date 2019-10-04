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

namespace MultiSafepay\Connect\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class MultisafepayTokenization extends AbstractModel implements
    IdentityInterface
{
    const CACHE_TAG = 'multiSafepay_connect_multisafepaytokenization';
    protected $_cacheTag = 'multiSafepay_connect_multisafepaytokenization';
    protected $_eventPrefix = 'multiSafepay_connect_multisafepaytokenization';

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }

    /**
     * @param         $customerId
     * @param boolean $showNull
     *
     * @return integer
     */
    public function getIdsByCustomerId($customerId, $showNull = false)
    {
        $id = $this->getResource()->getIdsByCustomerId($customerId, $showNull);
        return $id;
    }

    /**
     * @param $hash
     *
     * @return integer
     */
    public function getIdByHash($hash)
    {
        $id = $this->getResource()->getIdByHash($hash);
        return $id;
    }

    /**
     * @param $array
     *
     * @return array
     */
    public function toOptionArray($array)
    {
        $returnArray = $this->getResource()->toOptionArray($array);
        return $returnArray;
    }

    public function hideRecurringExpiredIds($array = [])
    {

            $id = $this->getResource()->hideRecurringExpiredIds($array);
            return $id;
    }

    public function getIdByOrderId($orderId)
    {
        return $this->getResource()->getIdByOrderId($orderId);
    }


    protected function _construct()
    {
        $this->_init(
            'MultiSafepay\Connect\Model\ResourceModel\MultisafepayTokenization'
        );
    }
}
