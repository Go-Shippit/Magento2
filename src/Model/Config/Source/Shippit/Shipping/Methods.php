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

class Methods implements \Magento\Framework\Data\OptionSourceInterface
{
    const SERVICE_LEVEL_STANDARD = 'standard';
    const SERVICE_LEVEL_EXPRESS = 'express';
    const SERVICE_LEVEL_PRIORITY = 'priority';
    const SERVICE_LEVEL_ON_DEMAND = 'on_demand';
    const SERVICE_LEVEL_CC = 'click_and_collect';

    public static $serviceLevels = [
        'standard' => 'Standard',
        'express' => 'Express',
        'priority' => 'Priority',
        'on_demand' => 'On Demand',
        'click_and_collect' => 'Click and Collect',
    ];

    public static $couriers = [
        'FourPXStandard' => '4px Standard',
        'AlliedExpressOvernight' => 'Allied Express',
        'AlliedExpressSameday' => 'Allied Express Same Day',
        'AramexExpress' => 'Aramex Express',
        'AramexInternational' => 'Aramex International',
        'eparcel' => 'Auspost eParcel',
        'eparcelexpress' => 'Auspost eParcel Express',
        'eparcelinternational' => 'Auspost eParcel International',
        'eparcelinternationalexpress' => 'Auspost eParcel International Express',
        'eParcelOndemand' => 'Auspost eParcel On Demand',
        'bonds' => 'Bonds Couriers',
        'couriersplease' => 'Couriers Please',
        'DawnWingStandard' => 'Dawn Wing',
        'DawnWingExpress' => 'Dawn Wing Express',
        'DesignerTransport' => 'Designer Transport',
        'dhlecommerce' => 'DHL eCommerce',
        'dhl' => 'DHL Express',
        'dhlexpress' => 'DHL Express Domestic',
        'dhlexpressinternational' => 'DHL Express International',
        'DirectCouriers' => 'Direct Couriers',
        'DirectFreightExpress' => 'DFE Express',
        'DpdExpress' => 'DPD Express',
        'Fastway' => 'Fastway',
        'FastwayNewZealand' => 'FastWay New Zealand',
        'HunterExpress' => 'Hunter Express',
        'InXpress' => 'InXpress Domestic',
        'InXpressInternational' => 'InXpress International',
        'KerryStandard' => 'Kerry Standard',
        'KerryExpress' => 'Kerry Express',
        'Neway' => 'Neway',
        'NinjaVanStandard' => 'Ninja Van Standard',
        'NinjaVanExpress' => 'Ninja Van Express',
        'NewZealandPost' => 'NZ Post',
        'NewZealandPostExpress' => 'NZ Post Express',
        'plainlabel' => 'Plain Label',
        'plainlabelinternational' => 'Plain Label International',
        'StarTrack' => 'StarTrack',
        'StarTrackPremium' => 'StarTrackPremium',
        'SekoStandard' => 'Seko Logistics',
        'SekoExpress' => 'Seko Logistics Express',
        'SingPost' => 'SingPost',
        'SingPostExpress' => 'SingPost Express',
        'Skybox' => 'SkyBox',
        'Tnt' => 'TNT',
        'TntOvernightExpress' => 'TNT Overnight Express',
        'Toll' => 'Toll',
        'UberOndemand' => 'Uber On Demand',
        'YelloOndemand' => 'Yello On Demand',
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
