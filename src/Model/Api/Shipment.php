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

class Shipment extends \Magento\Framework\Model\AbstractModel
{
    const ERROR_SHIPMENT_FAILED = 'The shipment could not be created.';

    /**
     * @var \Shippit\Shipping\Helper\Sync\Shipping
     */
    protected $helper;

    /**
     * @var \Shippit\Shipping\Api\Request\ShipmentInterfaceFactory
     */
    protected $requestShipmentInterfaceFactory;

    /**
     * @var \Shippit\Shipping\Api\Data\SyncShipmentInterfaceFactory
     */
    protected $syncShipmentInterfaceFactory;

    /**
     * @var \Shippit\Shipping\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

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
     * TransactionFactory
     *
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

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
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->requestShipmentInterfaceFactory = $requestShipmentInterfaceFactory;
        $this->syncShipmentInterfaceFactory = $syncShipmentInterfaceFactory;
        $this->logger = $logger;
        $this->shipmentFactory = $shipmentFactory;
        $this->messageManager = $messageManager;
        $this->date = $date;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->appEmulation = $appEmulation;
        $this->transactionFactory = $transactionFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function run()
    {
        // get all stores, as we will emulate each storefront for integration run
        $stores = $this->storeManagerInterface->getStores();

        foreach ($stores as $store) {
            $storeId = $store->getId();

            // Start Store Emulation
            $this->appEmulation->startEnvironmentEmulation(
                $storeId,
                AppArea::AREA_ADMINHTML
            );

            if (!$this->helper->isActive()) {
                return $this;
            }

            // get shipments to sync
            $syncShipments = $this->getSyncShipments($storeId);

            foreach ($syncShipments as $syncShipment) {
                $this->sync($syncShipment);
            }

            // // Stop Store Emulation
            $this->appEmulation->stopEnvironmentEmulation();
        }
    }

    /**
     * Get a list of sync orders pending sync
     */
    public function getSyncShipments($storeId)
    {
        return $this->syncShipmentInterfaceFactory->create()
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
     * @param \Shippit\Shipping\Model\Request\Shipment $syncShipment
     * @param boolean $displayNotifications
     * @return bool
     */
    public function sync($syncShipment, $displayNotifications = false)
    {
        try {
            // increase the attempt count by 1
            $syncShipment->setAttemptCount($syncShipment->getAttemptCount() + 1);
            $products = $syncShipment->getItemsCollection()->toArray()['items'];

            $shipmentRequest = $this->requestShipmentInterfaceFactory->create()
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
                ->setSyncedAt($this->date->gmtDate())
                ->save();
        }
        catch (Exception $e) {
            $this->logger->error('Shipment Sync Request Failed - ' . $e->getMessage());

            // Fail the sync item if it's breached the max attempts
            if ($syncShipment->getAttemptCount() > SyncShipment::SYNC_MAX_ATTEMPTS) {
                $syncShipment->setStatus(SyncShipment::STATUS_FAILED);
            }

            // save the sync item attempt count
            $syncShipment->save();

            if ($displayNotifications) {
                $this->messageManager->addError(__('Shipment for Order ' . $syncShipment->getOrderIncrement() . ' was not created - ' . $e->getMessage()));
            }

            return false;
        }

        return true;
    }

    /**
     * Create shipment record
     * @param  \Magento\Sales\Api\Data\OrderInterface $order
     * @param  array $items
     * @param  string $courierName
     * @param  string $trackingNumber
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     */
    protected function _createShipment($order, $items, $courierName, $trackingNumber)
    {
        $shipment = $this->shipmentFactory
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
        $transaction = $this->transactionFactory->create();

        $transaction->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        return $shipment;
    }
}
