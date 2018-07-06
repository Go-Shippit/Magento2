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
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
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

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            //code to upgrade to 1.0.4
            $this->upgrade_104($installer);
        }

        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            //code to upgrade to 1.0.5
            $this->upgrade_105($installer);
        }

        if (version_compare($context->getVersion(), '1.1.21') < 0) {
            //code to upgrade to 1.1.21
            $this->upgrade_1121($installer);
        }

        if (version_compare($context->getVersion(), '1.2.6') < 0) {
            //code to upgrade to 1.2.6
            $this->upgrade_126($installer);
        }

        if (version_compare($context->getVersion(), '1.4.0') < 0) {
            //code to upgrade to 1.4.0
            $this->upgrade_140($installer);
        }

        if (version_compare($context->getVersion(), '1.4.5') < 0) {
            //code to upgrade to 1.4.5
            $this->upgrade_145($installer);
        }

        $installer->endSetup();
    }

    /**
     * Upgrade the db schema to v1.0.4
     *
     * - Fix the shippit_sync_order field sync_order_id type + length
     * - Fix the shippit_sync_order field order_id type + length
     * - Update the shippit_sync_order field order_id index
     * - Update the shippit_sync_order fields attempt_count and status
     * - Add a new shippit_sync_order_items table
     *
     * @param $installer
     * @return void
     */
    public function upgrade_104($installer)
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

        // drop the foreign key while we adjust indexes
        $installer->getConnection()
            ->dropForeignKey(
                $installer->getTable('shippit_sync_order'),
                $installer->getFkName(
                    'shippit_sync_order',
                    'order_id',
                    'sales_order',
                    'entity_id'
                )
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

        // re-add the foreign key once all indexes are updated
        $installer->getConnection()
            ->addForeignKey(
                $installer->getFkName(
                    'shippit_sync_order',
                    'order_id',
                    'sales_order',
                    'entity_id'
                ),
                $installer->getTable('shippit_sync_order'),
                'order_id',
                $installer->getTable('sales_order'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
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

    /**
     * Upgrade the db schema to v1.0.5
     *
     * - Add the api_key to the shippit_sync_order table
     *   (used for custom implementations where a specific
     *    api key must be used for some orders)
     * - Update the defaults for the attempt_count and status columns
     *
     * @param $installer
     * @return void
     */
    public function upgrade_105($installer)
    {
        // Update Order Schema
        // add api key to the order schema
        $installer->getConnection()
            ->addColumn(
                $installer->getTable('shippit_sync_order'),
                'api_key',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'API Key',
                    'after' => 'sync_order_id'
                ]
            );

        // change defaults to status and attempt count values
        $installer->getConnection()
            ->changeColumn(
                $installer->getTable('shippit_sync_order'),
                'attempt_count',
                'attempt_count',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'comment' => 'Attempt Count',
                    'default' => '0'
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
                    'comment' => 'Status',
                    'default' => '0'
                ]
            );
    }

    /**
     * Upgrade the db schema to v1.1.21
     *
     * - Add authority to leaved and delivery instructions
     *   to the quotes and sales orders tables
     *
     * @param $installer
     * @return void
     */
    public function upgrade_1121($installer)
    {
        $installer->startSetup();

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'shippit_authority_to_leave',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Shippit - Authority To Leave',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'shippit_delivery_instructions',
            [
                'type' => 'text',
                'nullable' => true,
                'default' => null,
                'comment' => 'Shippit - Delivery Instructions'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'shippit_authority_to_leave',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Shippit - Authority To Leave',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'shippit_delivery_instructions',
            [
                'type' => 'text',
                'nullable' => true,
                'default' => null,
                'comment' => 'Shippit - Delivery Instructions'
            ]
        );

        $installer->endSetup();
    }

    /**
     * Upgrade the db schema to v1.2.6
     *
     * - Add dimensions fields to the shippit_sync_order_item table
     *
     * @param $installer
     * @return void
     */
    public function upgrade_126($installer)
    {
        $installer->startSetup();

        $table = $installer->getTable('shippit_sync_order_item');

        $installer->getConnection()->addColumn(
            $table,
            'length',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,2',
                'nullable' => true,
                'after' => 'weight',
                'comment' => 'Item Dimension - Length',
            ]
        );

        $installer->getConnection()->addColumn(
            $table,
            'width',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,2',
                'nullable' => true,
                'after' => 'length',
                'comment' => 'Item Dimension - Width',
            ]
        );

        $installer->getConnection()->addColumn(
            $table,
            'depth',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,2',
                'nullable' => true,
                'after' => 'width',
                'comment' => 'Item Dimension - Depth',
            ]
        );

        $installer->endSetup();
    }

    /**
     * Upgrade the db schema to v1.4.0
     *
     * - Create the shippit_sync_shipment and shippit_sync_shipment_item
     *   processing queue tables for shipments webhook processing
     *
     * @param $installer
     * @return void
     */
    public function upgrade_140($installer)
    {

        $installer->startSetup();

        // Create a shipment table
        $shipmentTable = $installer->getConnection()
            ->newTable($installer->getTable('shippit_sync_shipment'))
            ->addColumn(
                'sync_shipment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                array(
                    'identity'  => true,
                    'unsigned' => true,
                    'nullable'  => false,
                    'primary'   => true,
                ),
                'Id'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                array(
                    'unsigned'  => true,
                ),
                'Store Id'
            )
            ->addColumn(
                'order_increment',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                array(
                    'unsigned'  => true,
                    'default'   => null,
                    'nullable'  => true,
                ),
                'Order Increment'
            )
            ->addColumn(
                'shipment_increment',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                array(
                    'unsigned'  => true,
                    'default'   => null,
                    'nullable'  => true,
                ),
                'Shipment Increment'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                array(
                    'unsigned'  => true,
                    'nullable'  => false,
                ),
                'Status'
            )
            ->addColumn(
                'courier_allocation',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                array(),
                'Courier Allocation'
            )
            ->addColumn(
                'track_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                array(),
                'Tracking Number'
            )
            ->addColumn(
                'attempt_count',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                array(
                    'unsigned'  => true,
                    'nullable'  => false,
                ),
                'Attempt Count'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                array('nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE),
                'Created At'
            )
            ->addColumn(
                'synced_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                array(),
                'Synced At'
            )
            ->addIndex(
                $installer->getIdxName(
                    'shippit_sync_shipment',
                    ['store_id']
                ),
                ['store_id']
            )
            ->addIndex(
                $installer->getIdxName(
                    'shippit_sync_shipment',
                    ['order_increment']
                ),
                ['order_increment']
            )
            ->addIndex(
                $installer->getIdxName(
                    'shippit_sync_shipment',
                    ['shipment_increment']
                ),
                ['shipment_increment']
            )
            ->addIndex(
                $installer->getIdxName(
                    'shippit_sync_shipment',
                    ['track_number']
                ),
                ['track_number']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'shippit_sync_shipment',
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL,
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Shippit Shipment Sync History');

        $response = $installer->getConnection()->createTable($shipmentTable);
        // end of create shipment table

        // Create a shipment item table
        $shipmentItemTable = $installer->getConnection()
            ->newTable($installer->getTable('shippit_sync_shipment_item'))
            ->addColumn(
                'sync_shipment_item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                array(
                    'identity'  => true,
                    'unsigned' => true,
                    'nullable'  => false,
                    'primary'   => true,
                ),
                'Id'
            )
            ->addColumn(
                'sync_shipment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                array(
                    'unsigned'  => true,
                    'nullable'  => false,
                ),
                'Shipment Sync Id'
            )
            ->addColumn(
                'sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                array(
                    'nullable'  => false,
                ),
                'Item SKU'
            )
            ->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                array(),
                'Item Name'
            )
            ->addColumn(
                'qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                array(
                    'default' => '0.0000',
                ),
                'Item Qty'
            )
            ->addIndex(
                $installer->getIdxName(
                    'shippit_sync_shipment_item',
                    ['sync_shipment_id']
                ),
                ['sync_shipment_id']
            )
            ->addIndex(
                $installer->getIdxName(
                    'shippit_sync_shipment_item',
                    ['sku']
                ),
                ['sku']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'shippit_sync_shipment_item',
                    'sync_shipment_id',
                    'shippit_sync_shipment',
                    'sync_shipment_id'
                ),
                'sync_shipment_id',
                $installer->getTable('shippit_sync_shipment'),
                'sync_shipment_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Shippit Shipment Items Sync History');

        $installer->getConnection()->createTable($shipmentItemTable);
        //end of create shipment items table

        $installer->endSetup();
    }

    /**
     * Upgrade schema to v1.4.5
     *
     * - Adds the tariff_code column to the shippit_sync_order_item table
     *
     * @param $installer
     * @return void
     */
    public function upgrade_145($installer)
    {
        $installer->startSetup();

        $table = $installer->getTable('shippit_sync_order_item');

        $installer->getConnection()->addColumn(
            $table,
            'tariff_code',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '255',
                'nullable' => true,
                'after' => 'location',
                'comment' => 'Item Tariff Code',
            ]
        );

        $installer->endSetup();
    }
}
