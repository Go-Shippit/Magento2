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
use Shippit\Shipping\Model\Sync\Shipment as SyncShipment;

class Shipment
{
    /**
     * @var \Shippit\Shipping\Helper\Sync\Shipping
     */
    protected $_helper;

    /**
     * @var \Shippit\Shipping\Api\Request\ShipmentInterfaceFactory
     */
    protected $_requestShipmentInterfaceFactory;

    /**
     * @var \Shippit\Shipping\Api\Data\SyncShipmentInterfaceFactory
     */
    protected $_syncShipmentInterfaceFactory;

    /**
     * @var \Shippit\Shipping\Logger\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    protected $_shipmentFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

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
     * TransactionFactory
     *
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $_transactionFactory;

    public function __construct(
        \Shippit\Shipping\Helper\Sync\Shipping $helper,
        \Shippit\Shipping\Api\Request\ShipmentInterfaceFactory $requestShipmentInterfaceFactory,
        \Shippit\Shipping\Api\Data\SyncShipmentInterfaceFactory $syncShipmentInterfaceFactory,
        \Shippit\Shipping\Logger\Logger $logger,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Store\Model\App\Emulation $appEmulation
    ) {
        $this->_helper = $helper;
        $this->_requestShipmentInterfaceFactory = $requestShipmentInterfaceFactory;
        $this->_syncShipmentInterfaceFactory = $syncShipmentInterfaceFactory;
        $this->_logger = $logger;
        $this->_shipmentFactory = $shipmentFactory;
        $this->_messageManager = $messageManager;
        $this->_date = $date;
        $this->_storeManagerInterface = $storeManagerInterface;
        $this->_appEmulation = $appEmulation;
        $this->_transactionFactory = $transactionFactory;
    }

    public function run()
    {
        // get all stores, as we will emulate each storefront for integration run
        $stores = $this->_storeManagerInterface->getStores();

        foreach ($stores as $store) {
            $storeId = $store->getStoreId();

            // Start Store Emulation
            $this->_appEmulation->startEnvironmentEmulation(
                $storeId,
                AppArea::AREA_ADMINHTML
            );

            if (!$this->_helper->isActive()) {
                continue;
            }

            // get shipments to sync
            $syncShipments = $this->getSyncShipments($storeId);

            foreach ($syncShipments as $syncShipment) {
                $this->sync($syncShipment);
            }

            // // Stop Store Emulation
            $this->_appEmulation->stopEnvironmentEmulation();
        }
    }

    /**
     * Get a list of sync orders pending sync
     * @return \Shippit\Shipping\Model\ResourceModel\Sync\Shipment\Collection
     */
    public function getSyncShipments($storeId)
    {
        return $this->_syncShipmentInterfaceFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'status',
                ['eq' => SyncShipment::STATUS_PENDING]
            )
            ->addFieldToFilter(
                'attempt_count',
                ['lteq' => SyncShipment::SYNC_MAX_ATTEMPTS]
            )
            ->addFieldToFilter(
                'store_id',
                ['eq' => $storeId]
            );
    }

    /**
     * Process each shipment queue record to create shipment record
     * @param  SyncShipment  $syncShipment
     * @param  bool $displayNotifications
     * @return bool
     */
    public function sync($syncShipment, $displayNotifications = false)
    {
        try {
            // increase the attempt count by 1
            $syncShipment->setAttemptCount($syncShipment->getAttemptCount() + 1);
            $products = $syncShipment->getItemsCollection()->toArray()['items'];

            $shipmentRequest = $this->_requestShipmentInterfaceFactory->create()
                ->setOrderByIncrementId($syncShipment->getOrderIncrement())
                ->processItems($products);

            $shipment = $this->_createShipment(
                $shipmentRequest->getOrder(),
                $shipmentRequest->getItems(),
                $syncShipment->getCourierAllocation(),
                $syncShipment->getTrackNumber()
            );

            // Update the shipment to be marked as synced
            $syncShipment->setStatus(SyncShipment::STATUS_SYNCED)
                ->setShipmentIncrement($shipment->getIncrementId())
                ->setSyncedAt($this->_date->gmtDate())
                ->save();

        } catch (Exception $e) {
            $this->_logger->addError('Shipment Sync Request Failed - ' . $e->getMessage());

            // Fail the sync item if it's breached the max attempts
            if ($syncShipment->getAttemptCount() > SyncShipment::SYNC_MAX_ATTEMPTS) {
                $syncShipment->setStatus(SyncShipment::STATUS_FAILED);
            }

            // save the sync item attempt count
            $syncShipment->save();

            if ($displayNotifications) {
                $this->_messageManager->addError(__('Shipment for Order ' . $syncShipment->getOrderIncrement() . ' was not created - ' . $e->getMessage()));
            }
            return false;
        }

        return true;
    }

    /**
     * Create shipment record
     * @param Order $order
     * @param array $items
     * @param string $courierName
     * @param string $trackingNumber
     * @return Shipment
     * @throws Exception
     */
    protected function _createShipment($order, $items, $courierName, $trackingNumber)
    {
        $shipment = $this->_shipmentFactory
            ->create(
                $order,
                $items,
                [
                    [
                        'carrier_code' => \Shippit\Shipping\Helper\Data::CARRIER_CODE,
                        'title' => $courierName,
                        'number' => $trackingNumber,
                    ],
                ]
            );

        if (!$shipment) {
            throw new Exception(self::ERROR_SHIPMENT_FAILED);
        }

        $comment = sprintf(
            'Your order has been shipped - your tracking number is %s',
            $trackingNumber
        );

        $shipment->addComment($comment, true)
            ->register();

        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->_transactionFactory->create();

        $transaction->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        return $shipment;
    }
}
