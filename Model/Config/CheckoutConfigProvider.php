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

namespace Shippit\Shipping\Model\Config;

use Magento\Checkout\Model\ConfigProviderInterface;

class CheckoutConfigProvider implements ConfigProviderInterface
{
    protected $helper;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        \Shippit\Shipping\Helper\Checkout $helper
    )
    {
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $hideCheckoutOptions = $this->helper->getHideCheckoutOptionsShippingMethods();

        $config = [
            'shippit' => [
                'hide_checkout_options' => [
                    'shipping_methods' => $hideCheckoutOptions,
                ],
            ],
        ];

        return $config;
    }
}
