<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace Shippit\Shipping\Model\Config\Source\Shippit;

class Environment implements \Magento\Framework\Option\ArrayInterface
{
    const LIVE =    'production';
    const STAGING = 'staging';
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'label' => 'Live',
                'value' => self::LIVE
            ),
            array(
                'label' => 'Sandbox',
                'value' => self::STAGING
            )
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            self::LIVE => 'Live',
            self::STAGING => 'Sandbox'
        );
    }
}