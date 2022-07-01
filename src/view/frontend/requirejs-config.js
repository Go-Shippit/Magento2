/*
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

var config = {
    config: {
        mixins: {
            // If the payload-extender is available, Shippit
            // will extend the payload via this mixin method
            // @see https://github.com/magento/magento2/pull/10991
            'Magento_Checkout/js/model/shipping-save-processor/payload-extender': {
                'Shippit_Shipping/js/model/shipping-save-processor/payload-extender': true
            },

            // Otherwise, Shippit will extend the shipping-save-processor,
            // adding a compatability layer that implements the
            // payload-extender for older versions of Magento
            'Magento_Checkout/js/model/shipping-save-processor/default': {
                'Shippit_Shipping/js/model/shipping-save-processor/default-compat': true
            },
            'Shippit_Shipping/js/model/shipping-save-processor/payload-extender-compat': {
                'Shippit_Shipping/js/model/shipping-save-processor/payload-extender': true
            },
        }
    },
};
