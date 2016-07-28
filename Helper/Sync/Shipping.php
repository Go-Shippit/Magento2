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

class Shipping extends \Shippit\Shipping\Helper\Data
{
    const XML_PATH_SETTINGS = 'shippit/sync_shipping/';

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
}
