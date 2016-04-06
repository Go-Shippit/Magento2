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

namespace Shippit\Shipping\Helper;

use Shippit\Shipping\Model\Config\Source\Shippit\Environment as ShippitEnvironment;

class Api extends \Magento\Framework\App\Helper\AbstractHelper
{
    const API_ENDPOINT_PRODUCTION = 'https://www.shippit.com/api/3';
    const API_ENDPOINT_STAGING = 'http://shippit-staging.herokuapp.com/api/3';
    const API_TIMEOUT = 5;
    const API_USER_AGENT = 'Shippit_Shipping for Magento2';

    protected $api;
    protected $logger;
    protected $helper;
    protected $apiUrl;

    /**
     * @param \Shippit\Shipping\Helper\Data $helper
     */
    public function __construct(
        \Shippit\Shipping\Helper\Data $helper,
        \Shippit\Shipping\Logger\Logger $logger
    ) {
        $this->helper = $helper;
        $this->logger = $logger;

        // We use Zend_Http_Client instead of Varien_Http_Client,
        // as Varien_Http_Client does not handle PUT requests correctly
        $this->api = new \Zend_Http_Client;
        $this->api->setConfig(
                array(
                    'timeout' => self::API_TIMEOUT,
                    'useragent' => self::API_USER_AGENT . ' v' . $this->helper->getModuleVersion(),
                )
            )
            ->setHeaders('Content-Type', 'application/json');
    }

    public function getApiEndpoint()
    {
        $environment = $this->helper->getEnvironment();

        if ($environment == ShippitEnvironment::LIVE) {
            return self::API_ENDPOINT_PRODUCTION;
        }
        else {
            return self::API_ENDPOINT_STAGING;
        }
    }

    public function getApiUri($path, $authToken = null)
    {
        if (is_null($authToken)) {
            $authToken = $this->helper->getApiKey();
        }

        return $this->getApiEndpoint() . '/' . $path . '?auth_token=' . $authToken;
    }

    public function call($uri, $requestData, $method = \Zend_Http_Client::POST, $exceptionOnResponseError = true)
    {
        $uri = $this->getApiUri($uri);

        $jsonRequestData = json_encode($requestData);

        if ($this->helper->isDebugActive()) {
            $this->logger->notice('-- SHIPPIT - API REQUEST: --');

            $this->logger->notice($uri);
            $this->logger->notice($jsonRequestData);
        }

        $apiRequest = $this->api
            ->setMethod($method)
            ->setUri($uri);

        if (!is_null($requestData)) {
            $apiRequest->setRawData($jsonRequestData);
        }

        try {
            $apiResponse = $apiRequest->request($method);
        }
        catch (Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Shippit_Shippit - An API Communication Error Occurred')
            );
        }

        if ($exceptionOnResponseError && $apiResponse->isError()) {
            $message = 'API Response Error' . "\n";
            $message .= 'Response: ' . $apiResponse->getStatus() . ' - ' . $apiResponse->getMessage() . "\n";
            
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Shippit_Shippit - '. $message)
            );
        }

        $apiResponseBody = json_decode($apiResponse->getBody());

        if ($this->helper->isDebugActive()) {
            $this->logger->notice('-- SHIPPIT - API RESPONSE --');
            $this->logger->notice(json_encode($apiResponse));
        }

        return $apiResponseBody;
    }

    public function getQuote($requestData)
    {
        $requestData = array(
            'quote' => $requestData->toArray()
        );

        return $this->call('quotes', $requestData)
            ->response;
    }

    public function sendOrder($requestData)
    {
        $requestData = array(
            'order' => $requestData->toArray()
        );

        return $this->call('orders', $requestData)
            ->response;
    }

    public function getMerchant()
    {
        return $this->call('merchant', null, \Zend_Http_Client::GET, false);
    }

    public function putMerchant($requestData, $exceptionOnResponseError = false)
    {
        $requestData = array(
            'merchant' => $requestData->toArray()
        );

        $url = $this->getApiUri('merchant');

        return $this->call('merchant', $requestData, \Zend_Http_Client::PUT, $exceptionOnResponseError)
            ->response;
    }
}