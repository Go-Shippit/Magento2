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

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Shippit\Shipping\Model\Config\Source\Shippit\Environment as ShippitEnvironment;

class Api extends \Magento\Framework\App\Helper\AbstractHelper
{
    const API_ENDPOINT_PRODUCTION = 'https://app.shippit.com/api/3/';
    const API_ENDPOINT_STAGING = 'https://app.staging.shippit.com/api/3/';
    const API_TIMEOUT = 30;

    /**
     * @var \Shippit\Shipping\Helper\Data
     */
    protected $helper;

    /**
     * @var \Shippit\Shipping\Logger\Logger
     */
    protected $logger;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @param \Shippit\Shipping\Helper\Data $helper
     * @param \Shippit\Shipping\Logger\Logger $logger
     */
    public function __construct(
        \Shippit\Shipping\Helper\Data $helper,
        \Shippit\Shipping\Logger\Logger $logger
    ) {
        $this->logger = $logger;
        $this->helper = $helper;

        $clientHandler = HandlerStack::create();
        $clientHandler->push(
            $this->logRequestsMiddleware($this->logger),
            'log-requests'
        );

        $this->client = new Client(
            [
                'base_uri' => (
                    $this->helper->getEnvironment() == ShippitEnvironment::PRODUCTION
                    ? self::API_ENDPOINT_PRODUCTION
                    : self::API_ENDPOINT_STAGING
                ),
                'handler' => $clientHandler,
                'timeout' => self::API_TIMEOUT,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent' => $this->getUserAgent(),
                    'Authorization' => sprintf(
                        'Bearer %s',
                        $this->helper->getApiKey()
                    ),
                ],
            ]
        );
    }

    /**
     * Retrieve a quote
     *
     * @param \Shippit\Shipping\Api\Request\QuoteInterface $quoteRequestData
     * @return object
     */
    public function createQuote($quoteRequestData)
    {
        $apiResponse = $this->client->post(
            'quotes',
            [
                RequestOptions::JSON => [
                    'quote' => $quoteRequestData->toArray(),
                ],
            ]
        );

        return json_decode($apiResponse->getBody())
            ->response;
    }

    /**
     * Create an order
     *
     * @param \Shippit\Shipping\Model\Request\Order $orderRequestData
     * @return object
     */
    public function createOrder($orderRequestData)
    {
        $apiResponse = $this->client->post(
            'orders',
            [
                RequestOptions::JSON => [
                    'order' => $orderRequestData->toArray(),
                ],
            ]
        );

        return json_decode($apiResponse->getBody())
            ->response;
    }

    /**
     * Retrieve the merchant details
     *
     * @return object
     */
    public function getMerchant()
    {
        $apiResponse = $this->client->get('merchant');

        return json_decode($apiResponse->getBody())
            ->response;
    }

    /**
     * Update the merchant details
     *
     * @param object $merchantRequestData
     * @return object
     */
    public function updateMerchant($merchantRequestData)
    {
        $apiResponse = $this->client->put(
            'merchant',
            [
                RequestOptions::JSON => [
                    'merchant' => $merchantRequestData->toArray(),
                ],
            ]
        );

        return json_decode($apiResponse->getBody())
            ->response;
    }

    /**
     * Retrieve the user agent for outbound API calls
     *
     * @return string
     */
    protected function getUserAgent()
    {
        return sprintf(
            'Shippit_Magento2/%s Magento/%s PHP/%s',
            $this->helper->getModuleVersion(),
            $this->helper->getMagentoVersion(),
            phpversion()
        );
    }

    protected function logRequestsMiddleware(LoggerInterface $logger)
    {
        return function (callable $handler) use ($logger) {
            return function (RequestInterface $request, array $options) use ($handler, $logger) {
                // Log the request
                $logger->info(
                    'Request:',
                    [
                        'method' => $request->getMethod(),
                        'uri' => $request->getUri(),
                        'body' => $request->getBody()->__toString(),
                    ]
                );

                return $handler($request, $options)->then(
                    function (ResponseInterface $response) use ($logger) {
                        // Log the response
                        $logger->info(
                            'Response:',
                            [
                                'statusCode' => $response->getStatusCode(),
                                'body' => $response->getBody()->__toString(),
                            ]
                        );

                        return $response;
                    }
                );
            };
        };
    }
}
