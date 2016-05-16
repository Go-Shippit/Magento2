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

namespace Shippit\Shipping\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ValidateMerchantObserver implements ObserverInterface
{
    const ERROR_API_KEY = 'Shippit configuration error: Please check the API Key';
    const ERROR_API_COMMUNICATION = 'Shippit API error: An error occured while communicating with the Shippit API';
    const NOTICE_API_KEY_VALID = 'Shippit API Key Validated';

    const ERROR_WEBHOOK_REGISTRATION = 'Shippit Webhook Registration Error: An error occured while registering the webhook with Shippit';
    const ERROR_WEBHOOK_REGISTRATION_UNKNOWN = 'Shippit Webhook Registration Error: An unknown error occured while registering the webhook with Shippit';
    const NOTICE_WEBHOOK_REGISTRATION_SUCCESS = '';

    protected $_helper;
    protected $_syncShippingHelper;
    protected $_api;
    protected $_logger;
    protected $_urlInterface;
    protected $_messageManager;
    protected $_storeManager;

    protected $_hasAttemptedSync = false;
 
    public function __construct (
        \Shippit\Shipping\Helper\Data $helper,
        \Shippit\Shipping\Helper\Sync\Shipping $syncShippingHelper,
        \Shippit\Shipping\Helper\Api $api,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Shippit\Shipping\Logger\Logger $logger
    ) {
        $this->_helper = $helper;
        $this->_syncShippingHelper = $syncShippingHelper;
        $this->_api = $api;
        $this->_urlInterface = $urlInterface;
        $this->_messageManager = $messageManager;
        $this->_storeManager = $storeManager;
        $this->_logger = $logger;
    }
 
    public function execute(Observer $observer)
    {
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

        if ($apiKeyValid) {
            $this->_registerWebhook();
        }
    }

    private function _registerWebhook()
    {
        if (!$this->_syncShippingHelper->isActive()) {
            return;
        }

        try {
            $apiKey = $this->_helper->getApiKey();

            // @todo webhook url generation based on url interface
            // $webhookUrl = $this->_urlInterface
            //     ->setScope(1)
            //     ->getUrl(
            //         'shippit/order/update',
            //         array(
            //             'api_key' => $apiKey,
            //             '_secure' => true,
            //         )
            //     );
            
            $webhookUrl = $this->_storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK)
                . 'shippit/order/update/api_key/' . $apiKey;
            
            $requestData = new \Magento\Framework\DataObject;
            $requestData->setWebhookUrl($webhookUrl);
            $merchant = $this->_api->putMerchant($requestData, true);

            if (property_exists($merchant, 'error')) {
                $this->_messageManager->addError(self::ERROR_WEBHOOK_REGISTRATION_ERROR . ' ' . $merchant->error);
            }
            else {
                $this->_logger->addNotice(self::NOTICE_WEBHOOK_REGISTRATION_SUCCESS . ' ' . $webhookUrl);
                $this->_messageManager->addSuccess(self::NOTICE_WEBHOOK_REGISTRATION_SUCCESS . ' ' . $webhookUrl);
            }
        }
        catch (\Exception $e) {
            $this->_logger->addError(self::ERROR_WEBHOOK_REGISTRATION_UNKNOWN . ' ' . $e->getMessage());
            $this->_messageManager->addError(self::ERROR_WEBHOOK_REGISTRATION_UNKNOWN . ' ' . $e->getMessage());
        }
    }
}