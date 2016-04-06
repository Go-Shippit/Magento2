<?php
/**
 *  Shippit Pty Ltd
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the terms
 *  that is available through the world-wide-web at this URL:
 *  http://www.shippit.com/terms
 *
 *  @category   Shippit
 *  @copyright  Copyright (c) 2016 by Shippit Pty Ltd (http://www.shippit.com)
 *  @author     Matthew Muscat <matthew@mamis.com.au>
 *  @license    http://www.shippit.com/terms
 */

namespace Shippit\Shipping\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ValidateMerchantObserver implements ObserverInterface
{
    protected $helper;
    protected $syncShippingHelper;
    protected $api;
    protected $logger;
    protected $request;
    protected $messageManager;

    protected $_hasAttemptedSync = false;
 
    public function __construct (
        \Shippit\Shipping\Helper\Data $helper,
        \Shippit\Shipping\Helper\Sync\Shipping $syncShippingHelper,
        \Shippit\Shipping\Helper\Api $api,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Shippit\Shipping\Logger\Logger $logger
    ) {
        $this->helper = $helper;
        $this->syncShippingHelper = $syncShippingHelper;
        $this->api = $api;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }
 
    public function execute(Observer $observer)
    {
        if ($this->request->getParam('section') != 'shippit') {
            return;
        }

        try {
            $apiKeyValid = false;

            $merchant = $this->api->getMerchant();

            if (property_exists($merchant, 'error')) {
                if ($merchant->error == 'invalid_merchant_account') {
                    $this->logger->log('Shippit configuration error: Please check the API Key');
                    $this->messageManager->addError('Shippit configuration error: Please check the API Key');
                }
                else {
                    $this->logger->log('Shippit API error: ' . $merchant->error);
                    $this->messageManager->addError('Shippit API error: ' . $merchant->error);
                }
            }
            else {
                $this->messageManager->addSuccess('Shippit API Key Validated');
                
                $apiKeyValid = true;
            }
        }
        catch (Exception $e) {
            $this->logger->log('Shippit API error: An error occured while communicating with the Shippit API');
            $this->messageManager->addError('Shippit API error: An error occured while communicating with the Shippit API');
        }
    }
}