define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        'Shippit_Shipping/js/model/shipping-rates-validator',
        'Shippit_Shipping/js/model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        shippingRatesValidator,
        shippingRatesValidationRules
    ) {
        'use strict';
        defaultShippingRatesValidator.registerValidator('shippit', shippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('shippit', shippingRatesValidationRules);
        return Component;
    }
);