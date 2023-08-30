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

class QuoteMethods implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'Standard',
                'value' => 'standard',
            ],
            [
                'label' => 'Express',
                'value' => 'express',
            ],
            [
                'label' => 'Priority',
                'value' => 'priority',
            ],
            [
                'label' => 'On Demand',
                'value' => 'on_demand',
            ],
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
            'standard' => 'Standard',
            'express' => 'Express',
            'priority' => 'Priority',
            'on_demand' => 'On Demand',
        ];
    }
}
