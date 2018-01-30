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

namespace Shippit\Shipping\Controller\Order;

class Update extends \Magento\Framework\App\Action\Action
{
    const ERROR_SYNC_DISABLED = 'Shipping Sync is Disabled';
    const ERROR_API_KEY_MISSING = 'An API Key is required';
    const ERROR_API_KEY_MISMATCH = 'The API Key provided does not match the configured API Key';
    const ERROR_BAD_REQUEST = 'An invalid request was recieved';
    const ERROR_ORDER_MISSING = 'The order id requested was not found';
    const ERROR_ORDER_INVOICE = 'Cannot do shipment for the order separately from invoice.';
    const ERROR_ORDER_STATUS = 'The order id requested has an status that is not available for shipping';
    const NOTICE_SHIPMENT_STATUS = 'Ignoring the order status update, as we only respond to ready_for_pickup state';
    const ERROR_SHIPMENT_FAILED = 'The shipment record was not able to be created at this time, please try again.';
    const SUCCESS_SHIPMENT_CREATED = 'The shipment record was created successfully.';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Shippit\Shipping\Helper\Sync\Shipping
     */
    protected $_helper;

    /**
     * @var \Shippit\Shipping\Api\Data\SyncShipmentInterfaceFactory
     */
    protected $_syncShipmentInterfaceFactory;

    /**
     * @var \Shippit\Shipping\Logger\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager

    ) {
        $this->_resultPageFactory = $resultPageFactory;

        parent::__construct($context);

        $this->_jsonHelper = $this->_objectManager->create('Magento\Framework\Json\Helper\Data');
        $this->_helper = $this->_objectManager->create('Shippit\Shipping\Helper\Sync\Shipping');
        $this->_syncShipmentInterfaceFactory = $this->_objectManager->create('Shippit\Shipping\Api\Data\SyncShipmentInterfaceFactory');
        $this->_logger = $this->_objectManager->create('Shippit\Shipping\Logger\Logger');
        $this->_storeManager = $storeManager;
    }

    /**
     * Attempt the shipment update
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        if (!$this->_checkIsActive()) {
            return;
        }

        if (!$this->_checkApiKey()) {
            return;
        }

        $request = $this->_getRequest();

        $this->_logRequest($request);

        if (!$this->_checkRequest($request)) {
            return;
        }

        try {
            // attempt to retrieve request data values for the shipment
            $orderIncrement = $this->_getOrderIncrement($request);
            $storeId = $this->_getStoreId($request);
            $courierName = $this->_getCourierName($request);
            $trackingNumber = $this->_getTrackingNumber($request);
            $products = $this->_getProducts($request);

            $this->_syncShipmentInterfaceFactory->create()
                ->setOrderIncrement($orderIncrement)
                ->setStoreId($storeId)
                ->setCourierAllocation($courierName)
                ->setTrackNumber($trackingNumber)
                ->addItems($products)
                ->save();

            $response = $this->_prepareResponse(
                true,
                self::SUCCESS_SHIPMENT_CREATED
            );

            return $this->getResponse()->setBody($response);
        }
        catch (\Exception $e)
        {
            $response = $this->_prepareResponse(false, $e->getMessage());
            $this->_logger->addError($e);

            return $this->getResponse()->setBody($response);
        }
    }

    protected function _checkIsActive()
    {
        if (!$this->_helper->isActive()) {
            $response = $this->_prepareResponse(
                false,
                self::ERROR_SYNC_DISABLED
            );

            $this->getResponse()->setBody($response);

            return false;
        }

        return true;
    }

    protected function _checkApiKey()
    {
        $apiKey = $this->getRequest()->getParam('api_key');

        if (empty($apiKey)) {
            $response = $this->_prepareResponse(
                false,
                self::ERROR_API_KEY_MISSING
            );

            $this->getResponse()->setBody($response);

            return false;
        }

        $configuredApiKey = $this->_helper->getApiKey();

        if ($configuredApiKey != $apiKey) {
            $response = $this->_prepareResponse(
                false,
                self::ERROR_API_KEY_MISMATCH
            );

            $this->getResponse()->setBody($response);

            return false;
        }

        return true;
    }

    protected function _getRequest()
    {
        if (!empty(file_get_contents('php://input'))) {
            return $this->_jsonHelper
                ->jsonDecode(file_get_contents('php://input'));
        } else {
            return array();
        }
    }

    protected function _logRequest($request = array())
    {
        $metaData = [
            'api_request' => [
                'request_body' => $request
            ]
        ];

        $this->_logger->addDebug('Shipment Sync Request Recieved', $metaData);
    }

    protected function _checkRequest($request = array())
    {
        if (empty($request)) {
            $response = $this->_prepareResponse(
                false,
                self::ERROR_BAD_REQUEST
            );

            $this->getResponse()->setBody($response);

            return false;
        }

        if (!isset($request['current_state']) || empty($request['current_state']) || $request['current_state'] != 'ready_for_pickup') {
            $response = $this->_prepareResponse(
                true,
                self::NOTICE_SHIPMENT_STATUS
            );

            $this->getResponse()->setBody($response);

            return false;
        }

        if (!isset($request['retailer_order_number']) || empty($request['retailer_order_number'])) {
            $response = $this->_prepareResponse(
                false,
                self::ERROR_ORDER_MISSING
            );

            $this->getResponse()->setBody($response);

            return false;
        }

        return true;
    }

    protected function _checkOrder($order)
    {
        if (!$order->getId()) {
            $response = $this->_prepareResponse(
                false,
                self::ERROR_ORDER_MISSING
            );

            $this->getResponse()->setBody($response);

            return false;
        }

        if ($order->getForcedShipmentWithInvoice()) {
            $response = $this->_prepareResponse(false, self::ERROR_ORDER_INVOICE);

            $this->getResponse()->setBody($response);

            return false;
        }

        if (!$order->canShip()) {
            $response = $this->_prepareResponse(
                false,
                self::ERROR_ORDER_STATUS
            );

            $this->getResponse()->setBody($response);

            return false;
        }

        return true;
    }

    protected function _getOrderIncrement($request = array())
    {
        if (!isset($request['retailer_order_number'])) {
            return false;
        }

        if (!empty($request['retailer_order_number'])) {
            return $request['retailer_order_number'];
        }
    }

    protected function _getStoreId($request = array())
    {
        return $this->_storeManager->getStore()->getStoreId();
    }

    protected function _getCourierName($request = array())
    {
        if (isset($request['courier_name'])) {
            return 'Shippit - ' . $request['courier_name'];
        } else {
            return 'Shippit';
        }
    }

    protected function _getTrackingNumber($request = array())
    {
        if (isset($request['tracking_number'])) {
            return $request['tracking_number'];
        }
        else {
            return 'N/A';
        }
    }

    protected function _getProducts($request = array())
    {
        if (empty($request['products'])) {
            return array();
        }

        $products = $request['products'];

        return array_map(
            function($product) {
                return array(
                    'sku' => $product['sku'],
                    'title' => $product['title'],
                    'qty' => $product['quantity']
                );
            },
            $products
        );
    }

    protected function _prepareResponse($success, $message)
    {
        $response = [
            'success' => $success,
            'message' => $message,
        ];

        $metaData = [
            'api_request' => [
                'request_body' => $this->_getRequest(),
                'response_body' => $response
            ]
        ];

        if ($success) {
            $this->_logger->addDebug($message, $metaData);
        } else {
            $this->_logger->addNotice($message, $metaData);
        }

        return $this->_jsonHelper->jsonEncode($response);
    }
}
