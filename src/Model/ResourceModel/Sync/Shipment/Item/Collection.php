<?php
/**
 * Shippit Pty Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the terms
 * that is available through the world-wide-web at this URL:
 * http://www.shippit.com/terms
 *
 * @category   Shippit
 * @copyright  Copyright (c) by Shippit Pty Ltd (http://www.shippit.com)
 * @author     Matthew Muscat <matthew@mamis.com.au>
 * @license    http://www.shippit.com/terms
 */

namespace Shippit\Shipping\Model\ResourceModel\Sync\Shipment\Item;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'sync_shipment_item_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Shippit\Shipping\Model\Sync\Shipment\Item',
            'Shippit\Shipping\Model\ResourceModel\Sync\Shipment\Item'
        );
    }

    /**
     * Filter the items collection by the SyncShipment
     * @param \Shippit\Shipping\Api\Data\SyncShipmentInterface $syncShipment The Sync Shipment Object
     */
    public function addSyncShipmentFilter(\Shippit\Shipping\Api\Data\SyncShipmentInterface $syncShipment)
    {
        $syncShipmentId = $syncShipment->getSyncShipmentId();

        if ($syncShipmentId) {
            $this->addFieldToFilter('sync_shipment_id', $syncShipmentId);
        }
        else {
            $this->addFieldToFilter('sync_shipment_id', null);
        }

        return $this;
    }
}
