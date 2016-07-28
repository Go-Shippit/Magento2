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

namespace Shippit\Shipping\Helper\Sync;

class Order extends \Shippit\Shipping\Helper\Data
{
    const XML_PATH_SETTINGS = 'shippit/sync_order/';

    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Return store config value for key
     *
     * @param   string $key
     * @return  string
     */
    public function getValue($key, $scope = 'website')
    {
        $path = self::XML_PATH_SETTINGS . $key;

        return $this->_scopeConfig->getValue($path, $scope);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return parent::isActive() && self::getValue('active');
    }

    public function getMode()
    {
        return self::getValue('mode');
    }

    public function getSendAllOrders()
    {
        return self::getValue('send_all_orders');
    }

    public function isProductLocationActive()
    {
        return self::getValue('product_location_active');
    }

    public function getProductLocationAttributeCode()
    {
        return self::getValue('product_location_attribute_code');
    }

    public function getShippingMethodMapping()
    {
        $values = unserialize( self::getValue('shipping_method_mapping'));
        $mappings = [];

        if (!empty($values)) {
            foreach ($values as $value) {
                $mappings[$value['shipping_method']] = $value['shippit_service_class'];
            }
        }

        return $mappings;
    }

    // Helper Methods
    public function getShippitShippingMethod($shippingMethod)
    {
        // If the shipping method is a shippit method,
        // processing using the selected shipping options
        if (strpos($shippingMethod, self::CARRIER_CODE) !== false) {
            $shippingOptions = str_replace(self::CARRIER_CODE . '_', '', $shippingMethod);
            $shippingOptions = explode('_', $shippingOptions);
            $courierData = [];
            
            if (isset($shippingOptions[0])) {
                $method = strtolower($shippingOptions[0]);

                // allows for legacy capability where
                // "priority" was referred to as "premium"
                if ($method == 'priority' || $method == 'premium') {
                    return 'priority';
                } else if ($method == 'express') {
                    return 'express';
                } else if ($method == 'standard') {
                    return 'standard';
                }
            }
        }
        
        // Use the mapping values and attempt to get a value
        $shippingMethodMapping = $this->getShippingMethodMapping();

        if (isset($shippingMethodMapping[$shippingMethod])
            && !empty($shippingMethodMapping[$shippingMethod])) {
            return $shippingMethodMapping[$shippingMethod];
        }

        // All options have failed, return false
        return false;
    }
}
