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

namespace MultiSafepay\Connect\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $tableName = $installer->getConnection()->getTableName(
            'sales_order_grid'
        );
        $columnName = 'multisafepay_status';
        if ($installer->getConnection()->isTableExists($installer->getTable($tableName))) {
            if (!$installer->getConnection()->tableColumnExists(
                $installer->getTable($tableName),
                $columnName
            )
            ) {
                $installer->getConnection()->addColumn(
                    $installer->getTable($tableName),
                    $columnName,
                    [
                        'type'    => Table::TYPE_TEXT,
                        'length'  => 255,
                        'comment' => 'MultiSafepay status'
                    ]
                );
            }
        }

        // Get multisafepay_tokenization table
        $tableName = $installer->getTable('multisafepay_tokenization');
        // Check if the table already exists
        if (!$installer->getConnection()->isTableExists($tableName)) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Customer ID'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Order Id'
                )
                ->addColumn(
                    'recurring_id',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true,],
                    'Token - Recurring ID'
                )
                ->addColumn(
                    'recurring_hash',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Recurring hash'
                )
                ->addColumn(
                    'cc_type',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Payment method'
                )
                ->addColumn(
                    'last_4',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Last 4'
                )
                ->addColumn(
                    'expiry_date',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Expiry Date'
                )
                ->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'Custom name'
                )->setComment("MultiSafepay Tokenization Table");
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
