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

namespace Shippit\Shipping\Model\ResourceModel\Sync;

class Shipment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('shippit_sync_shipment', 'sync_shipment_id');
    }

    /**
     * Save related items to the Sync Shipment
     *
     * @param \Magento\Framework\Model\AbstractModel $syncShipment
     * @return self
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $syncShipment)
    {
        $this->_saveItems($syncShipment);

        return parent::_afterSave($syncShipment);
    }

    /**
     * Save the items attached to the sync shipment
     *
     * @param \Shippit\Shipping\Model\Sync\Shipment $syncShipment
     * @return self
     */
    protected function _saveItems(\Shippit\Shipping\Model\Sync\Shipment $syncShipment)
    {
        foreach ($syncShipment->getItems() as $item) {
            $item->setSyncShipmentId($syncShipment->getId())
                ->save();
        }

        return $this;
    }
}
