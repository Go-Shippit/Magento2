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

class Order extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Shippit\Shipping\Helper\Sync\Order
     */
    protected $helper;

    /**
     * @var \Shippit\Shipping\Helper\Api
     */
    protected $api;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Shippit\Shipping\Model\Request\OrderFactory
     */
    protected $requestOrderFactory;

    /**
     * @var \Shippit\Shipping\Model\Sync\OrderFactory
     */
    protected $syncOrderFactory;

    /**
     * @var \Shippit\Shipping\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Store Manager Interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * App emulation model
     *
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $appEmulation;

    /**
     *
     * @param \Shippit\Shipping\Helper\Sync\Order $helper
     * @param \Shippit\Shipping\Helper\Api $api
     * @param \Shippit\Shipping\Model\Request\OrderFactory $requestOrderFactory
     * @param \Shippit\Shipping\Model\Sync\OrderFactory $syncOrderFactory
     * @param \Shippit\Shipping\Logger\Logger $logger
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
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
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->api = $api;
        $this->requestOrderFactory = $requestOrderFactory;
        $this->syncOrderFactory = $syncOrderFactory;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        $this->date = $date;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->appEmulation = $appEmulation;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function run()
    {
        if (!$this->helper->isActive()) {
            return $this;
        }

        // get all stores, as we will emulate each storefront for integration run
        $stores = $this->storeManagerInterface->getStores();

        foreach ($stores as $store) {
            $storeId = $store->getStoreId();

            // Start Store Emulation
            $this->appEmulation->startEnvironmentEmulation(
                $storeId,
                AppArea::AREA_ADMINHTML
            );

            $syncOrders = $this->getSyncOrders($storeId);

            foreach ($syncOrders as $syncOrder) {
                $this->sync($syncOrder);
            }

            // Stop Store Emulation
            $this->appEmulation->stopEnvironmentEmulation();
        }
    }

    /**
     * Get a list of sync orders pending sync
     */
    public function getSyncOrders($storeId)
    {
        $collection = $this->syncOrderFactory->create()
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
                (string) SyncOrder::STATUS_PENDING
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

    public function sync($syncOrder, $displayNotifications = false)
    {
        if (!$this->helper->isActive()) {
            return false;
        }

        try {
            // increase the attempt count by 1
            $syncOrder->setAttemptCount($syncOrder->getAttemptCount() + 1);
            $order = $syncOrder->getOrder();

            // Build the order request
            $orderRequest = $this->requestOrderFactory->create()
                ->processSyncOrder($syncOrder);

            $apiResponse = $this->api->createOrder($orderRequest);

            // Add the order tracking details to
            // the order comments and save
            $comment = __('Order Synced with Shippit - ' . $apiResponse->tracking_number);
            $order->addStatusHistoryComment($comment)
                ->setIsVisibleOnFront(false)
                ->save();

            // Update the order to be marked as synced
            $syncOrder->setStatus(SyncOrder::STATUS_SYNCED)
                ->setTrackingNumber($apiResponse->tracking_number)
                ->setSyncedAt($this->date->gmtDate())
                ->save();

            if ($displayNotifications) {
                $this->messageManager
                    ->addSuccess(
                        __(
                            'Order ' . $order->getIncrementId()
                                . ' Synced with Shippit - '
                                . $apiResponse->tracking_number
                        )
                    );
            }
        }
        catch (Exception $e) {
            $this->logger->error('API - Order Sync Request Failed - ' . $e->getMessage());

            // Fail the sync item if it's breached the max attempts
            if ($syncOrder->getAttemptCount() > SyncOrder::SYNC_MAX_ATTEMPTS) {
                $syncOrder->setStatus(SyncOrder::STATUS_FAILED);
            }

            // save the sync item attempt count
            $syncOrder->save();

            if ($displayNotifications) {
                $this->messageManager->addError(__('Order ' . $order->getIncrementId() . ' was not Synced with Shippit - ' . $e->getMessage()));
            }

            return false;
        }

        return true;
    }
}
