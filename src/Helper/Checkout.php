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

namespace Shippit\Shipping\Helper;

use Magento\Store\Model\ScopeInterface;

class Checkout extends \Shippit\Shipping\Helper\Data
{
    const XML_PATH_SETTINGS = 'shippit/checkout/';

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
    public function isAuthorityToLeaveActive()
    {
        return parent::isActive() && self::getValue('authority_to_leave_active');
    }

    /**
     * @return bool
     */
    public function isDeliveryInstructionsActive()
    {
        return parent::isActive() && self::getValue('delivery_instructions_active');
    }

    public function getHideCheckoutOptionsShippingMethods()
    {
        $shippingMethods = self::getValue('hide_checkout_options_shipping_methods');

        if (!empty($shippingMethods)) {
            return explode(',', $shippingMethods);
        }

        return [];
    }
}
