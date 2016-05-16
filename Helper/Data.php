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

namespace Shippit\Shipping\Helper;

use \Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CARRIER_CODE = 'shippit';
    const XML_PATH_SETTINGS = 'shippit/general/';

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
        return self::getValue('active');
    }

    public function getApiKey()
    {
        return self::getValue('api_key');
    }

    public function getEnvironment()
    {
        return self::getValue('environment');
    }

    public function isDebugActive()
    {
        return self::getValue('debug_active');
    }

    public function getModuleVersion()
    {
        // $moduleResource = new \Magento\Framework\Module\ModuleResource;
        // $moduleResource->getDataVersion('shippit_shipping');
        $version = '1.0.0';

        return $version;
    }

    /**
     * Attempts to get the region code (ie: VIC), using the postcode
     * Used as a fallback mechanism where the address does not contain region data
     * (ie: saved addresses with text based region, or a postcode estimate shipping request)
     *
     * @uses  Postcode ranges from https://en.wikipedia.org/wiki/Postcodes_in_Australia
     *
     * @param  string $postcode The postcode
     * @return mixed            The region code, or false if unable to determine
     */
    public function getRegionCodeFromPostcode($postcode)
    {
        $postcode = (int) $postcode;

        if ($postcode >= 1000 && $postcode <= 2599
            || $postcode >= 2619 && $postcode <= 2899
            || $postcode >= 2921 && $postcode <= 2999) {
            return 'NSW';
        }
        elseif ($postcode >= 200 && $postcode <= 299
            || $postcode >= 2600 && $postcode <= 2618
            || $postcode >= 2900 && $postcode <= 2920) {
            return 'ACT';
        }
        elseif ($postcode >= 3000 && $postcode <= 3999
            || $postcode >= 8000 && $postcode <= 8999) {
            return 'VIC';
        }
        elseif ($postcode >= 4000 && $postcode <= 4999
            || $postcode >= 9000 && $postcode <= 9999) {
            return 'QLD';
        }
        elseif ($postcode >= 5000 && $postcode <= 5799
            || $postcode >= 5800 && $postcode <= 5999) {
            return 'SA';
        }
        elseif ($postcode >= 6000 && $postcode <= 6797
            || $postcode >= 6800 && $postcode <= 6999) {
            return 'WA';
        }
        elseif ($postcode >= 7000 && $postcode <= 7799
            || $postcode >= 7800 && $postcode <= 7999) {
            return 'TAS';
        }
        elseif ($postcode >= 800 && $postcode <= 899
            || $postcode >= 900 && $postcode <= 999) {
            return 'NT';
        }
        else {
            return false;
        }
    }

    public function getState($state)
    {
        switch (strtolower(trim($state))) {
            case "victoria":
                $state = 'VIC';
                break;
            case "new south wales":
                $state = 'NSW';
                break;
            case "australian capital territory":
                $state = 'ACT';
                break;
            case "queensland":
                $state = 'QLD';
                break;
            case "south australia":
                $state = 'SA';
                break;
            case "western australia":
                $state = 'WA';
                break;
            case "tasmania":
                $state = 'TAS';
                break;
            case "northern territory":
                $state = 'NT';
                break;
            default:
                $state = null;
        }

        return $state;
    }
}