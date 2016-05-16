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
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $_orderInterface;

    /**
     * @var [type]
     */
    protected $_shipmentFactory;

    /**
     * @var [type]
     */
    protected $_shipmentSender;

    /**
     * @var \Magento\Sales\Api\Data\ShipmentTrackInterface
     */
    protected $_shipmentTrackInterface;

    /**
     * @var \Shippit\Shipping\Helper\Sync\Shipping
     */
    protected $_helper;

    /**
     * @var \Shippit\Shipping\Api\Request\ShipmentInterface
     */
    protected $_requestShipmentInterface;

    /**
     * @var \Shippit\Shipping\Logger\Logger
     */
    protected $_logger;
    
    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
        // \Magento\Framework\Json\Helper\Data $jsonHelper,
        // \Magento\Framework\DB\TransactionFactory $transactionFactory,
        // \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        // \Magento\Sales\Api\Data\ShipmentTrackInterface $shipmentTrackInterface,
        // \Shippit\Shipping\Helper\Sync\Shipping $helper
    ) {
        $this->_resultPageFactory = $resultPageFactory;

        parent::__construct($context);

        $this->_jsonHelper = $this->_objectManager->create('Magento\Framework\Json\Helper\Data');
        $this->_transactionFactory = $this->_objectManager->create('Magento\Framework\DB\TransactionFactory');
        $this->_orderInterface = $this->_objectManager->create('Magento\Sales\Api\Data\OrderInterface');
        $this->_shipmentFactory = $this->_objectManager->create('Magento\Sales\Model\Order\ShipmentFactory');
        $this->_shipmentSender = $this->_objectManager->create('Magento\Sales\Model\Order\Email\Sender\ShipmentSender');
        $this->_shipmentTrackInterface = $this->_objectManager->create('Magento\Sales\Api\Data\ShipmentTrackInterface');
        $this->_helper = $this->_objectManager->create('Shippit\Shipping\Helper\Sync\Shipping');
        $this->_requestShipmentInterface = $this->_objectManager->create('Shippit\Shipping\Api\Request\ShipmentInterface');
        $this->_logger = $this->_objectManager->create('Shippit\Shipping\Logger\Logger');
    }
    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        if (!$this->_helper->isActive()) {
            $response = $this->_prepareResponse(false, self::ERROR_SYNC_DISABLED);

            return $this->getResponse()->setBody($response);
        }

        $request = $this->_jsonHelper->jsonDecode(file_get_contents('php://input'));

        $metaData = array(
            'api_request' => array(
                'request_body' => $request
            )
        );

        $this->_logger->addDebug('Shipment Sync Request Recieved', $metaData);

        $apiKey = $this->getRequest()->getParam('api_key');
        $orderIncrementId = $request['retailer_order_number'];
        $orderShipmentState = $request['current_state'];
        $courierName = $request['courier_name'];
        $trackingNumber = $request['tracking_number'];

        if (isset($request['products'])) {
            $products = $request['products'];
        }
        else {
            $products = [];
        }

        if (empty($apiKey)) {
            $response = $this->_prepareResponse(false, self::ERROR_API_KEY_MISSING);

            return $this->getResponse()->setBody($response);
        }

        if (!$this->_checkApiKey($apiKey)) {
            $response = $this->_prepareResponse(false, self::ERROR_API_KEY_MISMATCH);

            return $this->getResponse()->setBody($response);
        }

        if (empty($request)) {
            $response = $this->_prepareResponse(false, self::ERROR_BAD_REQUEST);

            return $this->getResponse()->setBody($response);
        }

        if (empty($orderShipmentState) || $orderShipmentState != 'ready_for_pickup') {
            $response = $this->_prepareResponse(true, self::NOTICE_SHIPMENT_STATUS);

            return $this->getResponse()->setBody($response);
        }

        // attempt to get the order using the reference provided
        $order = $this->_getOrder($orderIncrementId);

        if (!$order->getId()) {
            $response = $this->_prepareResponse(false, self::ERROR_ORDER_MISSING);

            return $this->getResponse()->setBody($response);
        }

        if ($order->getForcedShipmentWithInvoice()) {
            $response = $this->_prepareResponse(false, self::ERROR_ORDER_INVOICE);

            return $this->getResponse()->setBody($response);
        }

        if (!$order->canShip()) {
            $response = $this->_prepareResponse(false, self::ERROR_ORDER_STATUS);

            return $this->getResponse()->setBody($response);
        }

        try {
            $shipmentRequest = $this->_requestShipmentInterface
                ->setOrder($order)
                ->processItems($products);

            // create the shipment
            $response = $this->_createShipment(
                $shipmentRequest->getOrder(),
                $shipmentRequest->getItems(),
                $courierName,
                $trackingNumber
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

    private function _prepareResponse($success, $message)
    {
        $response = [
            'success' => $success,
            'message' => $message,
        ];

        $metaData = [
            'api_request' => [
                'request_body' => $this->_jsonHelper->jsonDecode(file_get_contents('php://input')),
                'response_body' => $response
            ]
        ];

        if ($success) {
            $this->_logger->addDebug($message, $metaData);
        }
        else {
            $this->_logger->addNotice($message, $metaData);
        }

        return $this->_jsonHelper->jsonEncode($response);
    }

    private function _checkApiKey($apiKey)
    {
        $configuredApiKey = $this->_helper->getApiKey();
        
        if ($configuredApiKey != $apiKey) {
            return false;
        }
        
        return true;
    }

    private function _getOrder($orderIncrementId)
    {
        return $this->_orderInterface->load($orderIncrementId, 'increment_id');
    }

    private function _createShipment($order, $items, $courierName, $trackingNumber)
    {
        $shipment = $this->_shipmentFactory->create(
            $order,
            $items
        );

        if ($shipment) {
            $comment = 'Your order has been shipped - your tracking number is ' . $trackingNumber;

            $track = $this->_shipmentTrackInterface
                ->setNumber($trackingNumber)
                ->setCarrierCode(\Shippit\Shipping\Helper\Data::CARRIER_CODE)
                ->setTitle('Shippit - ' . $courierName);

            $shipment->addTrack($track)
                ->register()
                ->addComment($comment, true);

            try {
                $shipment->getOrder()->setIsInProcess(true);
                $transaction = $this->_transactionFactory->create();

                $transaction->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();

                $this->_shipmentSender->send($shipment);
            }
            catch (\Exception $e) {
                return $this->_prepareResponse(false, self::ERROR_SHIPMENT_FAILED . ' ' .$e->getMessage());
            }

            return $this->_prepareResponse(true, self::SUCCESS_SHIPMENT_CREATED);
        }
        
        return $this->_prepareResponse(false, self::ERROR_SHIPMENT_FAILED);
    }
}