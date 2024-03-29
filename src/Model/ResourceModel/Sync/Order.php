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

class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('shippit_sync_order', 'sync_order_id');
    }

    /**
     * Save related items to the Sync Order
     *
     * @param \Magento\Framework\Model\AbstractModel $syncOrder
     * @return self
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $syncOrder)
    {
        $this->_saveItems($syncOrder);

        return parent::_afterSave($syncOrder);
    }

    /**
     * Save the items attached to the sync order
     *
     * @param \Shippit\Shipping\Model\Sync\Order $syncOrder
     * @return self
     */
    protected function _saveItems(\Shippit\Shipping\Model\Sync\Order $syncOrder)
    {
        foreach ($syncOrder->getItems() as $item) {
            $item->setSyncOrderId($syncOrder->getId())
                ->save();
        }

        return $this;
    }
}
