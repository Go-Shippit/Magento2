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

namespace Shippit\Shipping\Controller\Order;

class Update extends \Magento\Framework\App\Action\Action
{
    const ERROR_SYNC_DISABLED = 'Shipping Sync is Disabled';
    const ERROR_API_KEY_MISSING = 'An API Key is required';
    const ERROR_API_KEY_MISMATCH = 'The API Key provided does not match the configured API Key';
    const ERROR_BAD_REQUEST = 'An invalid request was recieved';
    const ERROR_ORDER_MISSING = 'The order id requested was not found';
    const ERROR_ORDER_STATUS = 'The order id requested has an status that is not available for shipping';
    const NOTICE_SHIPMENT_STATUS = 'Ignoring the order status update, as we only respond to ready_for_pickup state';
    const ERROR_SHIPMENT_FAILED = 'The shipment record was not able to be created at this time, please try again.';
    const SUCCESS_SHIPMENT_CREATED = 'The shipment record was created successfully.';

    /** @var  \Magento\Framework\View\Result\Page */
    protected $resultPageFactory;

    protected $helper;
    protected $syncShippingHelper;
    
    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Shippit\Shipping\Helper\Data $helper,
        \Shippit\Shipping\Helper\Sync\Shipping $syncShippingHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->syncShippingHelper = $syncShippingHelper;
        
        parent::__construct($context);
    }
    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        if (!$this->helper->isActive()) {
            $response = $this->_prepareResponse(false, self::ERROR_SYNC_DISABLED);

            return $this->getResponse()->setBody($response);
        }

        $request = json_decode(file_get_contents('php://input'), true);

        $apiKey = $this->getRequest()->getParam('api_key');
        $orderIncrementId = $request['retailer_order_number'];
        $orderShipmentState = $request['current_state'];

        $courierName = $request['courier_name'];
        $trackingNumber = $request['tracking_number'];

        if (isset($request['products'])) {
            $products = $request['products'];
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

        try {
            $shipmentRequest = Mage::getModel('shippit/request_api_shipment')
                ->setOrderByIncrementId($orderIncrementId)
                ->processItems($products);

            $order = $shipmentRequest->getOrder();
            $items = $shipmentRequest->getItems();

            // create the shipment
            $response = $this->_createShipment($order, $items, $courierName, $trackingNumber);

            return $this->getResponse()->setBody($response);
        }
        catch (Exception $e)
        {
            $response = $this->_prepareResponse(false, $e->getMessage());

            return $this->getResponse()->setBody($response);
        }
    }

    private function _prepareResponse($success, $message)
    {
        return Mage::helper('core')->jsonEncode(array(
            'success' => $success,
            'message' => $message,
        ));
    }

    private function _getOrder($orderIncrementId)
    {
        return Mage::getModel('sales/order')->load($orderIncrementId, 'increment_id');
    }

    private function _checkApiKey($apiKey)
    {
        $configuredApiKey = Mage::helper('shippit')->getApiKey();
        
        if ($configuredApiKey != $apiKey) {
            return false;
        }
        
        return true;
    }

    private function _createShipment($order, $items, $courierName, $trackingNumber)
    {
        $shipment = $order->prepareShipment($items);

        $shipment = Mage::getModel('sales/service_order', $order)
            ->prepareShipment($items);

        if ($shipment) {
            $comment = 'Your order has been shipped - your tracking number is ' . $trackingNumber;

            $track = Mage::getModel('sales/order_shipment_track')
                ->setNumber($trackingNumber)
                ->setCarrierCode(Shippit_Shippit_Helper_Data::CARRIER_CODE)
                ->setTitle('Shippit - ' . $courierName);

            $shipment->addTrack($track)
                ->register()
                ->addComment($comment, true)
                ->setEmailSent(true);

            $shipment->getOrder()->setIsInProcess(true);

            try {
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();

                $shipment->sendEmail(true, $comment);
            }
            catch (Mage_Core_Exception $e) {
                return $this->_prepareResponse(false, self::ERROR_SHIPMENT_FAILED);
            }

            return $this->_prepareResponse(true, self::SUCCESS_SHIPMENT_CREATED);
        }

        return $this->_prepareResponse(false, self::ERROR_SHIPMENT_FAILED);
    }
}