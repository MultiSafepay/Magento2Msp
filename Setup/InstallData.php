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
use Magento\Framework\Notification\NotifierInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Setup\SalesSetupFactory;

class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Sales\Setup\SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @var \Magento\Framework\Notification\NotifierInterface
     */
    protected $notifier;

    /**
     * @param \Magento\Sales\Setup\SalesSetupFactory            $salesSetupFactory
     * @param \Magento\Framework\Notification\NotifierInterface $notifier
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        NotifierInterface $notifier
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->notifier = $notifier;
    }

    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();

        $this->notifier->addNotice(
            'MultiSafepay install',
            'MultiSafepay: Check out our documentation page to setup your webshop',
            'https://docs.multisafepay.com/integrations/magento2/manual/'
        );

        $tableName = $installer->getConnection()->getTableName(
            'sales_order_grid'
        );
        $columnName = 'multisafepay_status';

        if ($installer->getConnection()->isTableExists($installer->getTable($tableName))) {
            $salesSetup = $this->salesSetupFactory->create(
                ['resourceName' => 'sales_setup', 'setup' => $installer]
            );

            $salesSetup->addAttribute(
                Order::ENTITY,
                $columnName,
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'visible'  => false,
                    'nullable' => true
                ]
            );
        }

        $installer->endSetup();
    }
}
