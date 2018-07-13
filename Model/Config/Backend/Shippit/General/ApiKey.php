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

namespace Shippit\Shipping\Model\Config\Backend\Shippit\General;

class ApiKey extends \Magento\Framework\App\Config\Value
{
    const ERROR_API_KEY = 'Shippit configuration error: Please check the API Key';
    const ERROR_API_COMMUNICATION = 'Shippit API error: An error occured while communicating with the Shippit API';
    const NOTICE_API_KEY_VALID = 'Shippit API Key Validated';

    protected $_helper;
    protected $_syncShippingHelper;
    protected $_api;
    protected $_logger;
    protected $_messageManager;
    protected $_configInterface;
    protected $_storeManager;
    protected $_appEmulation;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param string $runModelPath
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ReinitableConfigInterface $configInterface,
        \Magento\Store\Model\App\Emulation $emulation,
        \Shippit\Shipping\Helper\Api $api,
        \Shippit\Shipping\Logger\Logger $logger,
        \Shippit\Shipping\Helper\Data $helper,
        \Shippit\Shipping\Helper\Sync\Shipping $syncShippingHelper,
        array $data = []
    ) {
        $this->_appEmulation = $emulation;
        $this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
        $this->_configInterface = $configInterface;
        $this->_api = $api;
        $this->_logger = $logger;
        $this->_helper = $helper;
        $this->_syncShippingHelper = $syncShippingHelper;

        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function afterSave()
    {
        if (!$this->isValueChanged()) {
            return $this;
        }

        $storeId = $this->getStoreId();
        $environment = $this->_appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_ADMINHTML);

        // re-init configuration
        $this->_configInterface->reinit();

        if (!$this->_helper->isActive()) {
            $this->_appEmulation->stopEnvironmentEmulation();
            return $this;
        }

        try {
            $apiKeyValid = false;

            $merchant = $this->_api->getMerchant();

            if (property_exists($merchant, 'error')) {
                if ($merchant->error == 'invalid_merchant_account') {
                    $this->_logger->addError(self::ERROR_API_KEY);
                    $this->_messageManager->addError(self::ERROR_API_KEY);
                }
                else {
                    $this->_logger->addError(self::ERROR_API_COMMUNICATION . ' ' . $merchant->error);
                    $this->_messageManager->addError(self::ERROR_API_COMMUNICATION . ' ' . $merchant->error);
                }
            }
            else {
                $this->_logger->addNotice(self::NOTICE_API_KEY_VALID);
                $this->_messageManager->addSuccess(self::NOTICE_API_KEY_VALID);

                $apiKeyValid = true;
            }
        }
        catch (\Exception $e) {
            $this->_logger->addError(self::ERROR_API_COMMUNICATION);
            $this->_messageManager->addError(self::ERROR_API_COMMUNICATION);
        }
        finally {
            $this->_appEmulation->stopEnvironmentEmulation();
        }

        return parent::afterSave();
    }

    /**
     * Get the store id of the scope currently being saved
     * @return int|null The store id that is currently being saved,
     *                  or null if saving at the default scope
     */
    public function getStoreId()
    {
        if ($this->getScope() == 'default') {
            return $this->getScopeId();
        }
        // If the current scope is a website, get
        // the default store id for the website
        elseif ($this->getScope() == \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES) {
            $websiteId = $this->getScopeId();
            $website = $this->_storeManager->getWebsite($websiteId);

            return $website->getDefaultStore()->getStoreId();
        }
        elseif ($this->getScope() == Magento\Store\Model\ScopeInterface::SCOPE_STORES) {
            return $this->getScopeId();
        }
    }
}
