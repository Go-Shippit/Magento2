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

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace Shippit\Shipping\Model\Config\Source\Shippit;

class Environment implements \Magento\Framework\Option\ArrayInterface
{
    const LIVE = 'production';
    const STAGING = 'staging';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'Live',
                'value' => self::LIVE
            ],
            [
                'label' => 'Sandbox',
                'value' => self::STAGING
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
            self::LIVE => 'Live',
            self::STAGING => 'Sandbox'
        ];
    }
}
