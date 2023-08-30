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

define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/quote'
    ],
    function ($, ko, Component, quote) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Shippit_Shipping/checkout/shipping/shippit'
            },

            initObservable: function () {
                this._super();

                this.canShowShippitShippingOptions = ko.computed(
                    function () {
                        // Retrieve the currently selected shipping method
                        var method = quote.shippingMethod();

                        // Combine the carrier_code + method code for the selected method code name
                        var selectedMethodName = method != null ? method.carrier_code + '_' + method.method_code : null;

                        // Retrieve the hide_checkout_options configuration
                        var hideCheckoutOptionShippingMethods = window.checkoutConfig.shippit.hide_checkout_options.shipping_methods;

                        // If the selected method is not found, show the checkout options
                        if (selectedMethodName == null) {
                            return true;
                        }

                        var canShowCheckoutOptions = true;

                        // If the selected method is in a listing of shipping method
                        // checkout option settings, hide the checkout options
                        hideCheckoutOptionShippingMethods.some(
                            function (hideCheckoutOptionShippingMethod) {
                                if (selectedMethodName.toLowerCase().startsWith(hideCheckoutOptionShippingMethod)) {
                                    canShowCheckoutOptions = false;

                                    return;
                                }
                            }
                        );

                        return canShowCheckoutOptions;
                    },
                    this
                );

                return this;
            },
        });
    }
);
