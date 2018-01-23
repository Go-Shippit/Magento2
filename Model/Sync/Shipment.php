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

namespace Shippit\Shipping\Model\Sync;

use Shippit\Shipping\Api\Data\SyncShipmentInterface;

class Shipment extends \Magento\Framework\Model\AbstractModel implements SyncShipmentInterface
{
    /* Sync Order Statuses */
    const STATUS_PENDING = 0;
    const STATUS_PENDING_TEXT = 'Pending';
    const STATUS_SYNCED = 1;
    const STATUS_SYNCED_TEXT = 'Synced';
    const STATUS_FAILED = 2;
    const STATUS_FAILED_TEXT = 'Failed';

    const SYNC_MAX_ATTEMPTS = 5;

    /**
     * An array of sync shipment items
     *
     * @var Array
     */
    protected $_items;

    /**
     * A collection of sync shipment items
     *
     * @var \Shippit\Shipping\Model\ResourceModel\Sync\Shipment\Item\Collection
     */
    protected $_itemsCollection;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipmentInterface
     * @param \Shippit\Shipping\Api\Data\SyncShipmentItemInterface $syncShipmentItemInterface
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Api\Data\ShipmentInterface $shipmentInterface,
        \Shippit\Shipping\Api\Data\SyncShipmentItemInterface $syncShipmentItemInterface,
        \Shippit\Shipping\Model\Sync\Shipment\ItemFactory $syncShipmentItemFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_shipmentInterface = $shipmentInterface;
        $this->_syncShipmentItemInterface = $syncShipmentItemInterface;
        $this->_syncShipmentItemFactory = $syncShipmentItemFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Shippit\Shipping\Model\ResourceModel\Sync\Shipment');
    }

    /**
     * Get the Sync Shipment Id
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getData(self::SYNC_SHIPMENT_ID);
    }

    /**
     * Set the Sync Shipment Id
     *
     * @param int $syncShipmentId
     * @return string|null
     */
    public function setId($syncShipmentId)
    {
        return $this->setData(self::SYNC_SHIPMENT_ID, $syncShipmentId);
    }

    /**
     * Get the Sync Shipment Id
     *
     * @return string|null
     */
    public function getSyncShipmentId()
    {
        return $this->getData(self::SYNC_SHIPMENT_ID);
    }

    /**
     * Set the Sync Shipment Id
     *
     * @param int $syncShipmentId
     * @return string|null
     */
    public function setSyncShipmentId($syncShipmentId)
    {
        return $this->setData(self::SYNC_SHIPMENT_ID, $syncShipmentId);
    }

    /**
     * Get the Order Id
     *
     * @return string|null
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set the Order Id
     *
     * @param string $orderId
     * @return string|null
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Get the Shipping Method
     *
     * @return string|null
     */
    public function getOrderIncrement()
    {
        return $this->getData(self::ORDER_INCREMENT);
    }

    /**
     * Set the Shipping Method
     *
     * @param string $shippingMethod
     * @return string|null
     */
    public function setOrderIncrement($orderIncrement)
    {
        return $this->setData(self::ORDER_INCREMENT, $orderIncrement);
    }

    /**
     * Get the Shipping Method
     *
     * @return string|null
     */
    public function getShipmentIncrement()
    {
        return $this->getData(self::SHIPMENT_INCREMENT);
    }

    /**
     * Set the Shipping Method
     *
     * @param string $shippingMethod
     * @return string|null
     */
    public function setShipmentIncrement($shipmentIncrement)
    {
        return $this->setData(self::SHIPMENT_INCREMENT, $shipmentIncrement);
    }

    /**
     * Get the status
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set the status
     *
     * @param string $status
     * @return string|null
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get the status
     *
     * @return string|null
     */
    public function getCourierAllocation()
    {
        return $this->getData(self::COURIER_ALLOCATION);
    }

    /**
     * Set the status
     *
     * @param string $status
     * @return string|null
     */
    public function setCourierAllocation($courierAllocation)
    {
        return $this->setData(self::COURIER_ALLOCATION, $courierAllocation);
    }

    /**
     * Get the tracking number
     *
     * @return string|null
     */
    public function getTrackNumber()
    {
        return $this->getData(self::TRACK_NUMBER);
    }

    /**
     * Set the tracking number
     *
     * @param string $trackNumber
     * @return string|null
     */
    public function setTrackNumber($trackNumber)
    {
        return $this->setData(self::TRACK_NUMBER, $trackNumber);
    }

    /**
     * Get the attempt count
     *
     * @return string|null
     */
    public function getAttemptCount()
    {
        return $this->getData(self::ATTEMPT_COUNT);
    }

    /**
     * Set the attempt count
     *
     * @param string $attemptCount
     * @return string|null
     */
    public function setAttemptCount($attemptCount)
    {
        return $this->setData(self::ATTEMPT_COUNT, $attemptCount);
    }


    /**
     * Get the attempt count
     *
     * @return string|null
     */
    public function getSyncedAt()
    {
        return $this->getData(self::SYNCED_AT);
    }

    /**
     * Set the attempt count
     *
     * @param string $attemptCount
     * @return string|null
     */
    public function setSyncedAt($syncedAt)
    {
        return $this->setData(self::SYNCED_AT, $syncedAt);
    }

    /**
     * Get the attempt count
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set the attempt count
     *
     * @param string $attemptCount
     * @return string|null
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getOrder()
    {
        if (!$this->_order instanceof $this->_orderInterface) {
            $this->_order = $this->_orderInterface->load($this->getOrderId());
        }

        return $this->_order;
    }

    /**
     * Retrieve sync shipment items collection
     *
     * @param   bool $useCache
     * @return  \Magento\Eav\Model\Entity\Collection\AbstractCollection;
     */
    public function getItemsCollection($useCache = true)
    {
        if ($this->_itemsCollection === null || !$useCache) {
            $this->_itemsCollection = $this->_syncShipmentItemInterface
                ->getCollection()
                ->addSyncShipmentFilter($this);
        }

        return $this->_itemsCollection;
    }

    /**
     * Retrieve the sync order items
     *
     * @return array
     */
    public function getItems()
    {
        $this->_items = $this->getItemsCollection()->getItems();

        return $this->_items;
    }

    /**
     * Add a new item to the sync shipment request
     *
     * @param \Shippit\Shipping\Api\Data\SyncShipmentItemInterface $item
     */
    public function addItem(\Shippit\Shipping\Api\Data\SyncShipmentItemInterface $item)
    {
        if (!$item->getSyncShipmentItemId()) {
            $this->getItemsCollection()->addItem($item);
            $this->_items[] = $item;
        }

        return $this;
    }

    /**
     * Add new items to the sync shipment request
     *
     * @param Array $items
     */
    public function addItems(array $items)
    {
        foreach ($items as $item) {
            $itemObject = $this->_syncShipmentItemFactory
                ->create()
                ->addItem($item);

            $this->addItem($itemObject);
        }

        return $this;
    }
}
