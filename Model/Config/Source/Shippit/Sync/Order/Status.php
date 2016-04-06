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

namespace Shippit\Shipping\Model\Config\Source\Shippit\Sync\Order;

use Shippit\Shipping\Model\Sync\Order as SyncOrder;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionsArray = array(
            array(
                'label' => SyncOrder::STATUS_PENDING_TEXT,
                'value' => SyncOrder::STATUS_PENDING
            ),
            array(
                'label' => SyncOrder::STATUS_SYNCED_TEXT,
                'value' => SyncOrder::STATUS_SYNCED
            ),
            array(
                'label' => SyncOrder::STATUS_FAILED_TEXT,
                'value' => SyncOrder::STATUS_FAILED
            )
        );
        
        return $optionsArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $optionsArray = array(
            SyncOrder::STATUS_PENDING => SyncOrder::STATUS_PENDING_TEXT,
            SyncOrder::STATUS_SYNCED => SyncOrder::STATUS_SYNCED_TEXT,
            SyncOrder::STATUS_FAILED => SyncOrder::STATUS_FAILED_TEXT
        );
        
        return $optionsArray;
    }
}