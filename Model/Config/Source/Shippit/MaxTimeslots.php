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

namespace Shippit\Shipping\Model\Config\Source\Shippit;

class MaxTimeslots implements \Magento\Framework\Option\ArrayInterface
{
    const TIMESLOTS_MIN = 1;
    const TIMESLOTS_MAX = 20;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $timeslots = range(self::TIMESLOTS_MIN, self::TIMESLOTS_MAX);

        $optionsArray = [];
        $optionsArray[] = [
            'label' => '-- No Max Timeslots --',
            'value' => ''
        ];

        foreach ($timeslots as $timeslot) {
            $optionsArray[] = [
                'label' => $timeslot . ' Timeslots',
                'value' => $timeslot
            ];
        }

        return $optionsArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $timeslots = range(self::TIMESLOTS_MIN, self::TIMESLOTS_MAX);

        $array = [];
        $array[''] = '-- No Max Timeslots --';

        foreach ($timeslots as $timeslot) {
            $array[$timeslot] = $timeslot . ' Timeslots';
        }

        return $array;
    }
}
