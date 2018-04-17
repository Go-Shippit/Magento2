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
                    'plain_label' => 'Plain Label',
                ]
            ],
            // Standard Carriers
            [
                'optgroup-name' => 'carriers-standard',
                'label' => 'Standard Couriers',
                'value' => [
                    'Bonds' => 'Bonds',
                    'Eparcel' => 'eParcel',
                    'Fastway' => 'Fastway',
                    'CouriersPlease' => 'Couriers Please',
                    'Tnt' => 'TNT',
                    'EparcelInternational' => 'eParcel International',
                    'StarTrack' => 'StarTrack',
                    'DhlEcommerce' => 'DHL eCommerce',
                ]
            ],
            // Express Carriers
            [
                'optgroup-name' => 'carriers-express',
                'label' => 'Express Couriers',
                'value' => [
                    'StarTrackPremium' => 'StarTrack Premium',
                    'EparcelExpress' => 'eParcel Express',
                    'DhlExpress' => 'DHL Express',
                    'DhlExpressInternational' => 'DHL Express International',
                    'EparcelInternationalExpress' => 'eParcel International Express',
                ]
            ],
            // Other Carriers
            [
                'optgroup-name' => 'carriers-others',
                'label' => 'Others',
                'value' => [
                    'PlainLabelInternational' => 'Plain Label International',
                ]
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
            // Shippit service levels
            'standard' => 'Standard',
            'express' => 'Express',
            'priority' => 'Priority',
            'click_and_collect' => 'Click and Collect',
            'plain_label' => 'Plain Label',
            // Shippit Carriers
            'Bonds' => 'Bonds',
            'Eparcel' => 'eParcel',
            'Fastway' => 'Fastway',
            'CouriersPlease' => 'Couriers Please',
            'Tnt' => 'TNT',
            'EparcelInternational' => 'eParcel International',
            'StarTrack' => 'StarTrack',
            'DhlEcommerce' => 'DHL eCommerce',
            'StarTrackPremium' => 'StarTrack Premium',
            'EparcelExpress' => 'eParcel Express',
            'DhlExpress' => 'DHL Express',
            'DhlExpressInternational' => 'DHL Express International',
            'EparcelInternationalExpress' => 'eParcel International Express',
            'PlainLabelInternational' => 'Plain Label International',
        ];
    }
}
