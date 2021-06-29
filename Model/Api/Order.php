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

namespace Shippit\Shipping\Model\Api;

use Exception;
use Magento\Framework\App\Area as AppArea;
use Shippit\Shipping\Model\Sync\Order as SyncOrder;

class Order
{
    /**
     * @var \Shippit\Shipping\Helper\Sync\Order
     */
    protected $_helper;

    /**
     * @var \Shippit\Shipping\Helper\Api
     */
    protected $_api;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Shippit\Shipping\Model\Request\OrderFactory
     */
    protected $_requestOrderFactory;

    /**
     * @var \Shippit\Shipping\Model\Sync\OrderFactory
     */
    protected $_syncOrderFactory;

    /**
     * @var \Shippit\Shipping\Logger\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * Store Manager Interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManagerInterface;

    /**
     * App emulation model
     *
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $_appEmulation;

    /**
     * @param \Shippit\Shipping\Helper\Data $helper
     */
    public function __construct(
        \Shippit\Shipping\Helper\Sync\Order $helper,
        \Shippit\Shipping\Helper\Api $api,
        \Shippit\Shipping\Model\Request\OrderFactory $requestOrderFactory,
        \Shippit\Shipping\Model\Sync\OrderFactory $syncOrderFactory,
        \Shippit\Shipping\Logger\Logger $logger,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Store\Model\App\Emulation $appEmulation
    ) {
        $this->_helper = $helper;
        $this->_api = $api;
        $this->_requestOrderFactory = $requestOrderFactory;
        $this->_syncOrderFactory = $syncOrderFactory;
        $this->_logger = $logger;
        $this->_messageManager = $messageManager;
        $this->_date = $date;
        $this->_storeManagerInterface = $storeManagerInterface;
        $this->_appEmulation = $appEmulation;
    }

    public function run()
    {
        if (!$this->_helper->isActive()) {
            return;
        }

        // get all stores, as we will emulate each storefront for integration run
        $stores = $this->_storeManagerInterface->getStores();

        foreach ($stores as $store) {
            $storeId = $store->getStoreId();

            // Start Store Emulation
            $this->_appEmulation->startEnvironmentEmulation(
                $storeId,
                AppArea::AREA_ADMINHTML
            );

            $syncOrders = $this->getSyncOrders($storeId);

            foreach ($syncOrders as $syncOrder) {
                $this->sync($syncOrder);
            }

            // Stop Store Emulation
            $this->_appEmulation->stopEnvironmentEmulation();
        }
    }

    /**
     * Get a list of sync orders pending sync
     * @return \Shippit\Shipping\Model\ResourceModel\Sync\Order\Collection
     */
    public function getSyncOrders($storeId)
    {
        $collection = $this->_syncOrderFactory->create()
            ->getCollection();

        return $collection
            ->join(
                ['order' => $collection->getTable('sales_order')],
                'order.entity_id = main_table.order_id',
                [],
                null,
                'left'
            )
            ->addFieldToFilter(
                'main_table.status',
                SyncOrder::STATUS_PENDING
            )
            ->addFieldToFilter(
                'main_table.attempt_count',
                ['lteq' => SyncOrder::SYNC_MAX_ATTEMPTS]
            )
            ->addFieldToFilter(
                'order.state',
                ['eq' => \Magento\Sales\Model\Order::STATE_PROCESSING]
            )
            ->addFieldToFilter(
                'order.store_id',
                ['eq' => $storeId]
            );
    }

    /**
     * @param SyncOrder $syncOrder
     * @param bool $displayNotifications
     * @return bool
     */
    public function sync($syncOrder, $displayNotifications = false)
    {
        if (!$this->_helper->isActive()) {
            return false;
        }

        try {
            // increase the attempt count by 1
            $syncOrder->setAttemptCount($syncOrder->getAttemptCount() + 1);
            $order = $syncOrder->getOrder();

            // Build the order request
            $orderRequest = $this->_requestOrderFactory->create()
                ->processSyncOrder($syncOrder);

            $apiResponse = $this->_api->sendOrder($orderRequest);

            // Add the order tracking details to
            // the order comments and save
            $comment = __('Order Synced with Shippit - ' . $apiResponse->tracking_number);
            $order->addStatusHistoryComment($comment)
                ->setIsVisibleOnFront(false)
                ->save();

            // Update the order to be marked as synced
            $syncOrder->setStatus(SyncOrder::STATUS_SYNCED)
                ->setTrackingNumber($apiResponse->tracking_number)
                ->setSyncedAt($this->_date->gmtDate())
                ->save();

            if ($displayNotifications) {
                $this->_messageManager
                    ->addSuccess(
                        __('Order ' . $order->getIncrementId()
                        . ' Synced with Shippit - '
                        . $apiResponse->tracking_number)
                    );
            }
        }
        catch (Exception $e) {
            $this->_logger->addError('API - Order Sync Request Failed - ' . $e->getMessage());

            // Fail the sync item if it's breached the max attempts
            if ($syncOrder->getAttemptCount() > SyncOrder::SYNC_MAX_ATTEMPTS) {
                $syncOrder->setStatus(SyncOrder::STATUS_FAILED);
            }

            // save the sync item attempt count
            $syncOrder->save();

            if ($displayNotifications) {
                $this->_messageManager->addError(__('Order ' . $order->getIncrementId() . ' was not Synced with Shippit - ' . $e->getMessage()));
            }

            return false;
        }

        return true;
    }
}
