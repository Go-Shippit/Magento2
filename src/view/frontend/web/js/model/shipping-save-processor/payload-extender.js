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
        'mage/utils/wrapper'
    ],
    function (
        jQuery,
        wrapper
    ) {
        'use strict';

        return function (processor) {
            return wrapper.wrap(processor, function (proceed, payload) {
                payload = proceed(payload);

                var shippitExtentionAttributes = {
                    shippit_authority_to_leave: (jQuery('#shippit-options [name="shippit_authority_to_leave"]').is(':checked') ? 1 : 0),
                    shippit_delivery_instructions: jQuery('#shippit-options [name="shippit_delivery_instructions"]').val()
                };

                payload.addressInformation.extension_attributes = _.extend(
                    payload.addressInformation.extension_attributes,
                    shippitExtentionAttributes
                );

                return payload;
            });
        };
    }
);
