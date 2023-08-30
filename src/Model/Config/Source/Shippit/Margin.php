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

class Margin implements \Magento\Framework\Data\OptionSourceInterface
{
    const NONE = '';
    const PERCENTAGE = 'percentage';
    const FIXED = 'fixed';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'No',
                'value' => self::NONE,
            ],
            [
                'label' => 'Yes - Percentage',
                'value' => self::PERCENTAGE,
            ],
            [
                'label' => 'Yes - Fixed Amount',
                'value' => self::FIXED,
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
            self::NONE => 'No',
            self::PERCENTAGE => 'Yes - Percentage',
            self::FIXED => 'Yes - Fixed Amount',
        ];
    }
}
