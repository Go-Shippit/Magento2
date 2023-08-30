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

namespace Shippit\Shipping\Plugin\Checkout;

class DeliveryInstructionsPlugin
{
    protected $helper;

    public function __construct (
        \Shippit\Shipping\Helper\Checkout $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        if (!$this->helper->isDeliveryInstructionsActive()) {
            return $jsLayout;
        }

        // @phpcs:disable Squiz.Arrays.ArrayBracketSpacing.SpaceBeforeBracket
        $jsLayout['components']
            ['checkout']
            ['children']
            ['steps']
            ['children']
            ['shipping-step']
            ['children']
            ['shippingAddress']
            ['children']
            ['shippingAdditional']
            ['children']
            ['shippit']
            ['children']
            ['shippit-options']
            ['children']
            ['shippit_delivery_instructions'] = [
                'component' => 'Magento_Ui/js/form/element/textarea',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/textarea',
                    'id' => 'delivery-instructions',
                ],
                'dataScope' => 'shippingAddress.shippit_delivery_instructions',
                'label' => 'Delivery Instructions',
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => [],
                'sortOrder' => 201,
                'id' => 'delivery-instructions',
            ];

        return $jsLayout;
    }
}
