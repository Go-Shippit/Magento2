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

namespace Shippit\Shipping\Model\Config\Source\Shippit\Sync\Order;

use Shippit\Shipping\Helper\Data;

class Mode implements \Magento\Framework\Option\ArrayInterface
{
    const REALTIME  = 'realtime';
    const SCHEDULED = 'cron';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionsArray = [
            [
                'label' => 'Realtime',
                'value' => self::REALTIME
            ],
            [
                'label' => 'Scheduled',
                'value' => self::SCHEDULED
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
            self::REALTIME => 'Realtime',
            self::SCHEDULED => 'Scheduled'
        ];
        
        return $optionsArray;
    }
}