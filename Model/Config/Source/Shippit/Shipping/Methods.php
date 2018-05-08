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
    const SERVICE_LEVEL_STANDARD = 'standard';
    const SERVICE_LEVEL_EXPRESS = 'express';
    const SERVICE_LEVEL_PRIORITY = 'priority';
    const SERVICE_LEVEL_CC = 'click_and_collect';

    public static $serviceLevels = [
        'standard' => 'Standard',
        'express' => 'Express',
        'priority' => 'Priority',
        'click_and_collect' => 'Click and Collect',
    ];

    public static $couriers = [
        'eparcel' => 'Auspost eParcel',
        'eparcelexpress' => 'Auspost eParcel Express',
        'eparcelinternational' => 'Auspost eParcel International',
        'eparcelinternationalexpress' => 'Auspost eParcel International Express',
        'couriersplease' => 'Couriers Please',
        'fastway' => 'Fastway',
        'startrack' => 'StarTrack',
        'startrackpremium' => 'StarTrackPremium',
        'tnt' => 'TNT',
        'dhlecommerce' => 'DHL eCommerce',
        'dhl' => 'DHL Express',
        'dhlexpress' => 'DHL Express Domestic',
        'dhlexpressinternational' => 'DHL Express International',
        'plainlabel' => 'Plain Label',
        'plainlabelinternational' => 'Plain Label International',
        'bonds' => 'Bonds Couriers',
    ];

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            // Service levels
            [
                'optgroup-name' => 'service_level',
                'label' => 'Service Level',
                'value' => self::$serviceLevels,
            ],
            // Couriers
            [
                'optgroup-name' => 'couriers',
                'label' => 'Couriers',
                'value' => self::$couriers,
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
        return array_merge(
            preg_filter('/^/', 'Service Level: ', self::$serviceLevels),
            preg_filter('/^/', 'Carrier: ', self::$couriers)
        );
    }
}
