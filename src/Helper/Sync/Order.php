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
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Module\ModuleList $moduleList,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;

        parent::__construct($scopeConfig, $moduleList, $productMetadata);
    }

    /**
     * Return store config value for key
     *
     * @param   string $key
     * @return  string
     */
    public function getValue($key, $scope = ScopeInterface::SCOPE_STORES)
    {
        $path = self::XML_PATH_SETTINGS . $key;

        return $this->scopeConfig->getValue($path, $scope);
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
        $values = $this->serializer->unserialize(
            self::getValue('shipping_method_mapping')
        );

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
        if (strpos($shippingMethod, self::CARRIER_CODE_CC) !== false ||
            strpos($shippingMethod, self::CARRIER_CODE_CC_LEGACY) !== false
        ) {
            return 'click_and_collect';
        }

        // If the shipping method is a shippit method,
        // processing using the selected shipping options
        if (strpos($shippingMethod, self::CARRIER_CODE) !== false) {
            $shippingOptions = str_replace(self::CARRIER_CODE . '_', '', $shippingMethod);
            $shippingOptions = explode('_', $shippingOptions);

            if (isset($shippingOptions[0])) {
                $method = strtolower($shippingOptions[0]);

                if ($method == 'ondemand') {
                    return 'on_demand';
                }
                // allows for legacy capability where
                // "priority" was referred to as "premium"
                elseif ($method == 'priority' || $method == 'premium') {
                    return 'priority';
                }
                elseif ($method == 'express') {
                    return 'express';
                }
                elseif ($method == 'standard') {
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
