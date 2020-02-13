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

namespace MultiSafepay\Connect\Model\ResourceModel;

class MultisafepayTokenization extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @param integer $customerId
     * @param bool    $showNull
     *
     * @return mixed
     */
    public function getIdsByCustomerId($customerId, $showNull = false)
    {
        $table = $this->getMainTable();
        $where = $this->getConnection()->quoteInto(
            "customer_id = ?",
            $customerId
        );
        if (!$showNull) {
            $showNull = $this->getConnection()->quoteInto(
                "recurring_id != ?",
                null
            );
        } else {
            $showNull = $this->getConnection()->quoteInto(
                "id > ?",
                0
            );
        }
        $sql = $this->getConnection()->select()->from($table, ['id'])
            ->where($where)->where($showNull);
        $id = $this->getConnection()->fetchAll($sql);
        return $id;
    }

    /**
     * @param string $hash
     *
     * @return integer
     */
    public function getIdByHash($hash)
    {
        $table = $this->getMainTable();
        $where = $this->getConnection()->quoteInto(
            "recurring_hash = ?",
            $hash
        );
        $sql = $this->getConnection()->select()->from($table, ['id'])
            ->where($where);
        $id = $this->getConnection()->fetchOne($sql);
        return $id;
    }

    /**
     * @param int|array $array
     *
     * @return int $id
     */
    public function hideRecurringExpiredIds($array)
    {
        $table = $this->getMainTable();

        $ids = $this->getConnection()->quoteInto(
            "id IN (?)",
            $array
        );

        $date = date('ym');

        $whereDate = $this->getConnection()->quoteInto(
            "expiry_date >= ?",
            $date
        );

        $sql = $this->getConnection()->select()->from($table, ['id'])
            ->where($ids)->where($whereDate);

        $id = $this->getConnection()->fetchAll($sql);

        return $id;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    public function toOptionArray($array)
    {
        $returndata = [];
        foreach ($array as $item) {
            if (isset($item['recurring_id'])
                && isset($item['recurring_hash'])
                && isset($item['cc_type'])
                && isset($item['last_4'])
                && isset($item['expiry_date'])
                && !is_null($item['recurring_id'])
            ) {
                array_push(
                    $returndata,
                    [
                        'value' => $item['recurring_hash'],
                        'label' => (!is_null($item['name'])) ? $item['name'] : "{$item['cc_type']} - {$item['last_4']}"
                    ]
                );
            }

        }

        return $returndata;
    }

    public function getIdByOrderId($orderId)
    {
        $table = $this->getMainTable();
        $where = $this->getConnection()->quoteInto(
            "order_id = ?",
            $orderId
        );
        $sql = $this->getConnection()->select()->from($table, ['id'])
            ->where($where);
        $id = $this->getConnection()->fetchOne($sql);
        return $id;
    }

    protected function _construct()
    {
        $this->_init('multisafepay_tokenization', 'id');
    }
}
