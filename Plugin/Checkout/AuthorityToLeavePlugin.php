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

namespace Shippit\Shipping\Plugin\Checkout;

class AuthorityToLeavePlugin
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
        array  $jsLayout
    ) {
        if (!$this->helper->isAuthorityToLeaveActive()) {
            return $jsLayout;
        }

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
            ['shippit_authority_to_leave']= [
                'component' => 'Magento_Ui/js/form/element/boolean',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/checkbox',
                    'id' => 'authority-to-leave'
                ],
                'dataScope' => 'shippingAddress.shippit_authority_to_leave',
                'label' => 'Authority To Leave',
                'description' => 'Yes - Allow my order to be delivered without requiring a signature',
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => [],
                'sortOrder' => 200,
                'id' => 'authority-to-leave'
            ];

        return $jsLayout;
    }
}
