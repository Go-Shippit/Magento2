<?php
/**
 *  Shippit Pty Ltd
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the terms
 *  that is available through the world-wide-web at this URL:
 *  http://www.shippit.com/terms
 *
 *  @category   Shippit
 *  @copyright  Copyright (c) 2016 by Shippit Pty Ltd (http://www.shippit.com)
 *  @author     Matthew Muscat <matthew@mamis.com.au>
 *  @license    http://www.shippit.com/terms
 */

namespace Shippit\Shipping\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrade DB schema for the Shippit Shipping Module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            //code to upgrade to 1.0.3
            $this->upgrade_103($installer);
        }

        $installer->endSetup();
    }

    // Upgrade to v 1.0.3
    public function upgrade_103($installer)
    {
        // Update Order Schema
        // ensure sync_order_id is correctly typed/lengthed
        $installer->getConnection()
            ->changeColumn(
                $installer->getTable('shippit_sync_order'),
                'sync_order_id',
                'sync_order_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'identity' => true,
                    'primary' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment' => 'Sync Order ID'
                ]
            );

        // ensure order_id is correctly typed/lengthed
        $installer->getConnection()
            ->changeColumn(
                $installer->getTable('shippit_sync_order'),
                'order_id',
                'order_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment' => 'Order ID'
                ]
            );

        // drop unique index on order_id
        $installer->getConnection()
            ->dropIndex(
                $installer->getTable('shippit_sync_order'),
                $installer->getIdxName(
                    'shippit_sync_order',
                    ['order_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                )
            );

        // add standard index on order_id
        $installer->getConnection()
            ->addIndex(
                $installer->getTable('shippit_sync_order'),
                $installer->getIdxName(
                    'shippit_sync_order',
                    ['order_id']
                ),
                'order_id'
            );

        $installer->getConnection()
            ->changeColumn(
                $installer->getTable('shippit_sync_order'),
                'attempt_count',
                'attempt_count',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'comment' => 'Attempt Count'
                ]
            );

        $installer->getConnection()
            ->changeColumn(
                $installer->getTable('shippit_sync_order'),
                'status',
                'status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'comment' => 'Status'
                ]
            );

        // Add Order Item Schema
        $orderItemTable = $installer->getConnection()
            ->newTable($installer->getTable('shippit_sync_order_item'))
             ->addColumn(
                'sync_item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true
                ],
                'Sync Item ID'
            )
            ->addColumn(
                'sync_order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'unsigned' => true
                ],
                'Sync Order ID'
            )
            ->addColumn(
                'sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Item Sku'
            )
            ->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Item Name'
            )
            ->addColumn(
                'qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                null,
                [
                    'nullable' => false
                ],
                'Item Qty'
            )
            ->addColumn(
                'price',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                null,
                [
                    'nullable' => true,
                    'default' => null
                ],
                'Item Price'
            )
            ->addColumn(
                'weight',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                null,
                [
                    'nullable' => false
                ],
                'Item Weight'
            )
            ->addColumn(
                'location',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Item Location'
            )
            ->addIndex(
                $installer->getIdxName(
                    'shippit_sync_order_item',
                    ['sync_item_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['sync_item_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName(
                    'shippit_sync_order_item',
                    ['sync_order_id']
                ),
                ['sync_order_id']
            )
            ->addIndex(
                $installer->getIdxName(
                    'shippit_sync_order_item',
                    ['sku']
                ),
                ['sku']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'shippit_sync_order_item',
                    'sync_item_id',
                    'shippit_sync_order',
                    'sync_order_id'
                ),
                'sync_order_id',
                $installer->getTable('shippit_sync_order'),
                'sync_order_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Shippit - Sync Order Items');

        $installer->getConnection()->createTable($orderItemTable);
    }
}