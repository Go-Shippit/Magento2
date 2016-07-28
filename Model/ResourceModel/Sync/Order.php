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
 * @copyright  Copyright (c) 2016 by Shippit Pty Ltd (http://www.shippit.com)
 * @author     Matthew Muscat <matthew@mamis.com.au>
 * @license    http://www.shippit.com/terms
 */

namespace Shippit\Shipping\Model\ResourceModel\Sync;

/**
 * Sync Order MySQL Resource
 */
class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        $this->_init('shippit_sync_order', 'sync_order_id');
    }

    /**
     * Save related items to the Sync Order
     *
     * @param \Magento\Framework\Model\AbstractModel $customer
     * @return \Magento\Eav\Model\Entity\AbstractEntity
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $syncOrder)
    {
        $this->_saveItems($syncOrder);

        return parent::_afterSave($syncOrder);
    }

    /**
     * Save the items attached to the sync order
     *
     * @param  \Shippit\Shipping\Model\Sync\Order $syncOrder The Sync Order Object
     * @return \Shippit\Shipping\Model\Sync\Order The Sync Order Object
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