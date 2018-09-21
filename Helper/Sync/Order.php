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

namespace Shippit\Shipping\Helper\Sync;

use Magento\Store\Model\ScopeInterface;

class Order extends \Shippit\Shipping\Helper\Data
{
    const XML_PATH_SETTINGS = 'shippit/sync_order/';

    /**
     * Return store config value for key
     *
     * @param   string $key
     * @return  string
     */
    public function getValue($key, $scope = ScopeInterface::SCOPE_STORES)
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

    public function getShippingMethodMapping()
    {
        $values = $this->unserialize(self::getValue('shipping_method_mapping'));
        $mappings = [];

        // If the values are empty / not set, return early
        if (empty($values)) {
            return $mappings;
        }

        foreach ($values as $value) {
            $mappings[$value['shipping_method']] = $value['shippit_service_class'];
        }

        return $mappings;
    }

    // Helper Methods
    public function getShippitShippingMethod($shippingMethod)
    {
        if (strpos($shippingMethod, self::CARRIER_CODE_CC) !== FALSE) {
            return 'click_and_collect';
        }

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
                }
                else if ($method == 'express') {
                    return 'express';
                }
                else if ($method == 'standard') {
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

    /**
     * Add a method to unserialze data using either
     * Magento v2.0, v2.1 methods (PHP Object)
     * or the new Magento v2.2 (Json Object)
     */
    private function unserialize($value)
    {
        $unserialized = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $unserialized;
        }

        return unserialize($value);
    }
}
