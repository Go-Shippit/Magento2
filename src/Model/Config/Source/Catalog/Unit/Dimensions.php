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

namespace Shippit\Shipping\Model\Config\Source\Catalog\Unit;

class Dimensions implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        $optionsArray = [
            [
                'label' => 'Millimetres (mm)',
                'value' => \Shippit\Shipping\Helper\Sync\Order\Items::UNIT_DIMENSION_MILLIMETRES,
            ],
            [
                'label' => 'Centimetres (cm)',
                'value' => \Shippit\Shipping\Helper\Sync\Order\Items::UNIT_DIMENSION_CENTIMETRES,
            ],
            [
                'label' => 'Metres (m)',
                'value' => \Shippit\Shipping\Helper\Sync\Order\Items::UNIT_DIMENSION_METRES,
            ],
        ];

        return $optionsArray;
    }
}
