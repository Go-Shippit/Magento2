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

/**
 * Sync Order MySQL Resource
 */
class Shipment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
    }

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
     * @param \Magento\Framework\Model\AbstractModel $customer
     * @return \Magento\Eav\Model\Entity\AbstractEntity
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $syncShipment)
    {
        $this->_saveItems($syncShipment);

        return parent::_afterSave($syncShipment);
    }

    /**
     * Save the items attached to the sync shipment
     *
     * @param  \Shippit\Shipping\Model\Sync\Shipment $syncOrder The Sync Shipment Object
     * @return \Shippit\Shipping\Model\Sync\Shipment The Sync Shipment Object
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
