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

namespace Shippit\Shipping\Model;

class Shippit extends \Magento\Framework\Model\AbstractModel implements \Shippit\Shipping\Api\Request\ShippitInterface
{
    protected $_helper;
    protected $_syncOrder;
    protected $_requestSyncOrder;
    protected $_apiOrder;
    protected $_logger;
    protected $_order;
    protected $_messageManager;

    public function __construct (
        \Shippit\Shipping\Helper\Sync\Order $helper,
        \Shippit\Shipping\Api\Data\SyncOrderInterfaceFactory $syncOrder,
        \Shippit\Shipping\Api\Request\SyncOrderInterfaceFactory $requestSyncOrder,
        \Shippit\Shipping\Model\Api\Order $apiOrder,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_helper = $helper;
        $this->_syncOrder = $syncOrder;
        $this->_requestSyncOrder = $requestSyncOrder;
        $this->_apiOrder = $apiOrder;
        $this->_order = $order;
        $this->_messageManager = $messageManager;
    }

    /**
     * Adds the order to the request queue, and if the mode is requested as realtime,
     * attempts to sync the record immediately.
     *
     * Note: Priority shipping services are only available via live quoting
     *
     * @param object|integer $order         The order entity_id
     * @param array   $items                An array of the items to be included
     * @param string  $shippingMethod       The shipping method service class to be used (standard, express)
     * @param string  $apiKey               The API Key to be used in the request
     * @param string  $syncMode             The sync mode ot be used for the request
     * @param boolean $displayNotifications Flag to indiciate if notifications should be shown to the user
     */
    public function addOrder(
        $order,
        $items = [],
        $shippingMethod = null,
        $apiKey = null,
        $syncMode = null,
        $displayNotifications = false
    ) {
        // Ensure the module is active
        if (!$this->_helper->isActive()) {
            return $this;
        }

        // if the order passed is just an id, get the order object
        if (!$order instanceof $this->_order) {
            $order = $this->_order->load($order);
        }

        // if the order is a virtual order, skip it
        if ($order->getIsVirtual()) {
            if ($displayNotifications) {
                $this->_messageManager->addError(__('Order ' . $order->getIncrementId() . ' was not synced with Shippit, as this is a virtual order not requiring delivery'));
            }

            return $this;
        }

        $request = $this->_requestSyncOrder
            ->create()
            ->setOrder($order)
            ->setItems($items)
            ->setApiKey($apiKey)
            ->setShippingMethod($shippingMethod);

        // Create a new sync order record
        $syncOrder = $this->_syncOrder
            ->create()
            ->addSyncOrderRequest($request)
            ->save();

        // sync immediately if sync mode is realtime,
        if ($syncMode == \Shippit\Shipping\Model\Config\Source\Shippit\Sync\Order\Mode::REALTIME) {
            // return the result of the sync
            return $this->_apiOrder
                ->sync($syncOrder, $displayNotifications);
        }

        // return the sync order object
        return $syncOrder;
    }
}
