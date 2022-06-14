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

namespace Shippit\Shipping\Model\ResourceModel\Sync\Order\Item;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'sync_item_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Shippit\Shipping\Model\Sync\Order\Item',
            'Shippit\Shipping\Model\ResourceModel\Sync\Order\Item'
        );
    }

    /**
     * Filter the items collection by the SyncOrder
     * @param \Shippit\Shipping\Api\Data\SyncOrderInterface $syncOrder The Sync Order Object
     */
    public function addSyncOrderFilter(\Shippit\Shipping\Api\Data\SyncOrderInterface $syncOrder)
    {
        $syncOrderId = $syncOrder->getSyncOrderId();

        if ($syncOrderId) {
            $this->addFieldToFilter('sync_order_id', $syncOrderId);
        } else {
            $this->addFieldToFilter('sync_order_id', null);
        }

        return $this;
    }
}
