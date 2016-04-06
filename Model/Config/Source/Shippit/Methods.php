<?php
/**
*  Shippit Pty Ltd
*
*  NOTICE OF LICENSE
*
*  This source file is subject to the terms
*  that is available through the world-wide-web at this URL:
*  http://www.shippit.com/terms
*
*  @category   Shippit
*  @copyright  Copyright (c) 2016 by Shippit Pty Ltd (http://www.shippit.com)
*  @author     Matthew Muscat <matthew@mamis.com.au>
*  @license    http://www.shippit.com/terms
*/

namespace Shippit\Shipping\Model\Config\Source\Shippit;

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
        return array(
            array(
                'label' => 'Standard',
                'value' => 'standard'
            ),
            array(
                'label' => 'Express',
                'value' => 'express'
            ),
            array(
                'label' => 'Premium',
                'value' => 'premium'
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
            'standard' => 'Standard',
            'express' => 'Express',
            'premium' => 'Premium'
        );
    }
}