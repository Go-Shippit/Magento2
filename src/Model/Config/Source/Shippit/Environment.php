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

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace Shippit\Shipping\Model\Config\Source\Shippit;

class Environment implements \Magento\Framework\Data\OptionSourceInterface
{
    const PRODUCTION = 'production';
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
                'label' => 'Production',
                'value' => self::PRODUCTION,
            ],
            [
                'label' => 'Staging',
                'value' => self::STAGING,
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
            self::PRODUCTION => 'Production',
            self::STAGING => 'Staging',
        ];
    }
}
