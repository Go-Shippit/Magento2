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

namespace Shippit\Shipping\Model\Config\Source\Shippit\Sync\Shipment;

use Shippit\Shipping\Model\Sync\Shipment as SyncShipment;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionsArray = [
            [
                'label' => SyncShipment::STATUS_PENDING_TEXT,
                'value' => SyncShipment::STATUS_PENDING
            ],
            [
                'label' => SyncShipment::STATUS_SYNCED_TEXT,
                'value' => SyncShipment::STATUS_SYNCED
            ],
            [
                'label' => SyncShipment::STATUS_FAILED_TEXT,
                'value' => SyncShipment::STATUS_FAILED
            ]
        ];

        return $optionsArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $optionsArray = [
            SyncShipment::STATUS_PENDING => SyncShipment::STATUS_PENDING_TEXT,
            SyncShipment::STATUS_SYNCED => SyncShipment::STATUS_SYNCED_TEXT,
            SyncShipment::STATUS_FAILED => SyncShipment::STATUS_FAILED_TEXT
        ];

        return $optionsArray;
    }
}
