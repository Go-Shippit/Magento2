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

use Shippit\Shipping\Model\Config\Source\Shippit\Environment as ShippitEnvironment;
use Magento\Framework\Exception\LocalizedException;

class Api extends \Magento\Framework\App\Helper\AbstractHelper
{
    const API_ENDPOINT_PRODUCTION = 'https://www.shippit.com/api/3';
    const API_ENDPOINT_STAGING = 'https://staging.shippit.com/api/3';
    const API_TIMEOUT = 15;
    const API_USER_AGENT = 'Shippit_Shipping for Magento2';

    protected $_api;
    protected $_helper;
    protected $_logger;

    /**
     * @param \Shippit\Shipping\Helper\Data $helper
     */
    public function __construct(
        \Shippit\Shipping\Helper\Data $helper,
        \Shippit\Shipping\Logger\Logger $logger
    ) {
        $this->_helper = $helper;
        $this->_logger = $logger;

        $this->_api = new \Zend_Http_Client;
        $this->_api->setConfig(
                [
                    'timeout' => self::API_TIMEOUT,
                    'useragent' => self::API_USER_AGENT . ' v' . $this->_helper->getModuleVersion(),
                ]
            )
            ->setHeaders('Content-Type', 'application/json');
    }

    public function getApiEndpoint()
    {
        $environment = $this->_helper->getEnvironment();

        if ($environment == ShippitEnvironment::LIVE) {
            return self::API_ENDPOINT_PRODUCTION;
        } else {
            return self::API_ENDPOINT_STAGING;
        }
    }

    public function getApiUri($path, $authToken = null)
    {
        if ($authToken === null) {
            $authToken = $this->_helper->getApiKey();
        }

        return $this->getApiEndpoint() . '/' . $path . '?auth_token=' . $authToken;
    }

    public function call($uri, $requestData, $method = \Zend_Http_Client::POST, $exceptionOnResponseError = true)
    {
        $uri = $this->getApiUri($uri);
        $jsonRequestData = json_encode($requestData);
        $this->log($uri, $requestData);

        $apiRequest = $this->_api
            ->setMethod($method)
            ->setUri($uri);

        if ($requestData !== null) {
            $apiRequest->setRawData($jsonRequestData);
        }

        try {
            $apiResponse = $apiRequest->request($method);
        } catch (\Exception $e) {
            if (!isset($apiResponse)) {
                $apiResponse = null;
            }

            $this->log($uri, $requestData, $apiResponse, false, 'API Request Error');

            throw new LocalizedException(
                __('Shippit_Shipping - An API Communication Error Occurred')
            );
        }

        if ($exceptionOnResponseError && $apiResponse->isError()) {
            $message = 'API Response Error' . "\n";
            $message .= 'Response: ' . $apiResponse->getStatus() . ' - ' . $apiResponse->getMessage() . "\n";

            $this->log($uri, $requestData, $apiResponse, false, $message);

            throw new LocalizedException(
                __('Shippit_Shipping - '. $message)
            );
        }

        $this->log($uri, $requestData, $apiResponse);
        $apiResponseBody = json_decode($apiResponse->getBody());

        return $apiResponseBody;
    }

    protected function log($uri, $requestData, $apiResponse = null, $success = true, $message = 'Shippit API Request')
    {
        // add the request meta data
        $requestMetaData = [
            'api_request' => [
                'request_uri' => $uri,
                'request_body' => $requestData,
            ]
        ];

        if ($apiResponse !== null) {
            $requestMetaData['api_request']['response_code'] = $apiResponse->getStatus();
            $requestMetaData['api_request']['response_body'] = json_decode($apiResponse->getBody());
        }

        if ($success) {
            $this->_logger->addNotice($message, $requestMetaData);
        } else {
            $this->_logger->addError($message, $requestMetaData);
        }
    }

    public function getQuote($requestData)
    {
        $requestData = [
            'quote' => $requestData->toArray()
        ];

        return $this->call('quotes', $requestData)
            ->response;
    }

    public function sendOrder($requestData)
    {
        $requestData = [
            'order' => $requestData->toArray()
        ];

        return $this->call('orders', $requestData)
            ->response;
    }

    public function getMerchant()
    {
        return $this->call('merchant', null, \Zend_Http_Client::GET, false);
    }

    public function putMerchant($requestData, $exceptionOnResponseError = false)
    {
        $requestData = [
            'merchant' => $requestData->toArray()
        ];

        $url = $this->getApiUri('merchant');

        return $this->call('merchant', $requestData, \Zend_Http_Client::PUT, $exceptionOnResponseError)
            ->response;
    }
}
