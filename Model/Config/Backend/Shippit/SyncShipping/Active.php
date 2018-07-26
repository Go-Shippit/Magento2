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

namespace Shippit\Shipping\Model\Config\Backend\Shippit\SyncShipping;

use Exception;
use Magento\Framework\App\Area as AppArea;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

class Active extends \Magento\Framework\App\Config\Value
{
    const ERROR_WEBHOOK_REGISTRATION = 'Shippit Webhook Registration Error - An error occured while registering the webhook with Shippit';
    const ERROR_WEBHOOK_REGISTRATION_UNKNOWN = 'Shippit Webhook Registration Error - An unknown error occured while registering the webhook with Shippit';
    const NOTICE_WEBHOOK_REGISTRATION_SUCCESS = 'The Shippit Fulfillment Webhook was successfully registered';

    const VALUE_YES = 1;
    const VALUE_NO = 0;

    protected $_helper;
    protected $_syncShippingHelper;
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
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager,
     * @param \Magento\Framework\Message\ManagerInterface $messageManager,
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $configInterface,
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory,
     * @param \Magento\Store\Model\App\Emulation $appEmulation,
     * @param \Shippit\Shipping\Helper\Api $api,
     * @param \Shippit\Shipping\Logger\Logger $logger,
     * @param \Shippit\Shipping\Helper\Data $helper,
     * @param \Shippit\Shipping\Helper\Sync\Shipping $syncShippingHelper,
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
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Shippit\Shipping\Helper\Api $api,
        \Shippit\Shipping\Logger\Logger $logger,
        \Shippit\Shipping\Helper\Data $helper,
        \Shippit\Shipping\Helper\Sync\Shipping $syncShippingHelper,
        array $data = []
    ) {
        $this->_appEmulation = $appEmulation;
        $this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
        $this->_configInterface = $configInterface;
        $this->_dataObjectFactory = $dataObjectFactory;
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
        try {
            $storeId = $this->getStoreId();

            $this->_appEmulation->startEnvironmentEmulation(
                $storeId,
                AppArea::AREA_ADMINHTML
            );

            // re-init the system configuration to retrieve the latest values after save
            $this->_configInterface->reinit();

            $apiKey = $this->_helper->getApiKey();

            $webhookUrl = $this->_storeManager->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_WEB, true) . 'shippit/order/update/api_key/' . $apiKey;

            $requestData = $this->_dataObjectFactory->create();

            // if value is yes then create the webhook else delete the webhook
            if ($this->_helper->isActive() && $this->getValue() == self::VALUE_YES) {
                $requestData->setWebhookUrl($webhookUrl);
            }
            else {
                $requestData->setWebhookUrl();
            }

            $merchant = $this->_api->putMerchant($requestData, true);

            if (property_exists($merchant, 'error')) {
                $this->_messageManager->addError(self::ERROR_WEBHOOK_REGISTRATION_ERROR . ' - ' . $merchant->error);
            }
            else {
                $this->_logger->addNotice(self::NOTICE_WEBHOOK_REGISTRATION_SUCCESS . ' - ' . $webhookUrl);
                $this->_messageManager->addSuccess(self::NOTICE_WEBHOOK_REGISTRATION_SUCCESS . ' - ' . $webhookUrl);
            }
        }
        catch (Exception $e) {
            $this->_logger->addError(self::ERROR_WEBHOOK_REGISTRATION_UNKNOWN . ' - ' . $e->getMessage());
            $this->_messageManager->addError(self::ERROR_WEBHOOK_REGISTRATION_UNKNOWN . ' - ' . $e->getMessage());
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
    }
}
