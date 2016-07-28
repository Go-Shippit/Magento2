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

class SendAllOrders implements \Magento\Framework\Option\ArrayInterface
{
    const ALL = 'all';
    const ALL_LABEL = 'Yes - All Orders';

    const ALL_AU = 'all_au';
    const ALL_AU_LABEL = 'Yes - All Australian Orders';

    const NO = 'no';
    const NO_LABEL = 'No';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionsArray = [
            [
                'label' => self::ALL_LABEL,
                'value' => self::ALL
            ],
            [
                'label' => self::ALL_AU_LABEL,
                'value' => self::ALL_AU
            ],
            [
                'label' => self::NO_LABEL,
                'value' => self::NO
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
            self::ALL => self::ALL_LABEL,
            self::ALL_AU => self::ALL_AU_LABEL,
            self::NO => self::NO_LABEL
        ];
        
        return $optionsArray;
    }
}
