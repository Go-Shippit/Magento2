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

use Exception;
use Magento\Framework\App\Area as AppArea;
use Magento\Store\Model\ScopeInterface;

class ApiKey extends \Magento\Framework\App\Config\Value
{
    const ERROR_API_COMMUNICATION = 'Shippit API Error — An error occured while communicating with the Shippit API';
    const ERROR_API_KEY = 'Shippit API Key Validation Error - Please check the API Key';
    const NOTICE_API_KEY_VALID = 'Shippit API Key Validation was successful';

    protected $_helper;
    protected $_api;
    protected $_logger;
    protected $_messageManager;
    protected $_configInterface;
    protected $_dataObjectFactory;
    protected $_storeManager;
    protected $_appEmulation;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $configInterface
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Store\Model\App\Emulation $emulation
     * @param \Shippit\Shipping\Helper\Api $api
     * @param \Shippit\Shipping\Logger\Logger $logger
     * @param \Shippit\Shipping\Helper\Data $helper
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
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Store\Model\App\Emulation $emulation,
        \Shippit\Shipping\Helper\Api $api,
        \Shippit\Shipping\Logger\Logger $logger,
        \Shippit\Shipping\Helper\Data $helper,
        array $data = []
    ) {
        $this->_appEmulation = $emulation;
        $this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
        $this->_configInterface = $configInterface;
        $this->_dataObjectFactory = $dataObjectFactory;
        $this->_api = $api;
        $this->_logger = $logger;
        $this->_helper = $helper;

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
        try {
            $storeId = $this->getStoreId();

            $this->_appEmulation->startEnvironmentEmulation(
                $storeId,
                AppArea::AREA_ADMINHTML
            );

            // re-init the system configuration to retrieve the latest values after save
            $this->_configInterface->reinit();

            if (!$this->_helper->isActive()) {
                $this->_appEmulation->stopEnvironmentEmulation();

                return $this;
            }

            $merchant = $this->_api->getMerchant();

            if (property_exists($merchant, 'error')) {
                if ($merchant->error == 'invalid_merchant_account') {
                    $this->_logger->error(self::ERROR_API_KEY);
                    $this->_messageManager->addError(self::ERROR_API_KEY);
                }
                else {
                    $this->_logger->error(self::ERROR_API_COMMUNICATION . ' ' . $merchant->error);
                    $this->_messageManager->addError(self::ERROR_API_COMMUNICATION . ' ' . $merchant->error);
                }
            }
            else {
                $this->_logger->notice(self::NOTICE_API_KEY_VALID);
                $this->_messageManager->addSuccess(self::NOTICE_API_KEY_VALID);

                // Register the shipping cart name
                $this->registerShippingCartName();
            }
        }
        catch (Exception $e) {
            $this->_logger->error(self::ERROR_API_COMMUNICATION);
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
        elseif ($this->getScope() == ScopeInterface::SCOPE_WEBSITES) {
            $websiteId = $this->getScopeId();
            $website = $this->_storeManager->getWebsite($websiteId);

            return $website->getDefaultStore()->getStoreId();
        }
        elseif ($this->getScope() == ScopeInterface::SCOPE_STORES) {
            return $this->getScopeId();
        }

        return null;
    }

    public function registerShippingCartName()
    {
        try {
            $requestData = $this->_dataObjectFactory->create();
            $requestData->setShippingCartMethodName('magento2');

            $merchant = $this->_api->updateMerchant($requestData);
        }
        catch (Exception $e) {
            $this->_messageManager->addError('The request to update the shopping cart integration name failed - please try again.');
        }
    }
}
