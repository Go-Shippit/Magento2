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

namespace Shippit\Shipping\Model\Config\Source\Shippit\Shipping;

use Shippit\Shipping\Helper\Data;

class Methods implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            // Shippit service levels
            [
                'optgroup-name' => 'service_level',
                'label' => 'Service Level',
                'value' => [
                    'standard' => 'Standard',
                    'express' => 'Express',
                    'priority' => 'Priority',
                    'click_and_collect' => 'Click and Collect',
                    'plain_label' => 'Plain Label'
                ]
            ],
            // Shippit Carriers
            [
                'optgroup-name' => 'carriers',
                'label' => 'Carriers',
                'value' => [
                    'eparcel' => 'eParcel',
                    'fastway' => 'Fastway',
                    'couriers_please' => 'Couriers Please'
                ]
            ]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            // Shippit service levels
            'standard' => 'Standard',
            'express' => 'Express',
            'priority' => 'Priority',
            'click_and_collect' => 'Click and Collect',
            'plain_label' => 'Plain Label',
            // Shippit Carriers
            'eparcel' => 'eParcel',
            'fastway' => 'Fastway',
            'couriers_please' => 'Couriers Please'
        ];
    }
}
