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

use Shippit\Shipping\Api\Data\SyncOrderInterface;

class Order extends \Magento\Framework\Model\AbstractModel implements SyncOrderInterface
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
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $orderInterface;

    /**
     * @var \Shippit\Shipping\Api\Data\SyncOrderItemInterface
     */
    protected $syncOrderItemInterface;

    /**
     * @var \Shippit\Shipping\Model\Sync\Order\ItemFactory
     */
    protected $syncOrderItemFactory;

    /**
     * An instance of an order
     *
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $order;

    /**
     * An array of sync order items
     *
     * @var Array
     */
    protected $items;

    /**
     * A collection of sync order items
     *
     * @var \Shippit\Shipping\Model\ResourceModel\Sync\Order\Item\Collection
     */
    protected $itemsCollection;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Api\Data\OrderInterface $orderInterface
     * @param \Shippit\Shipping\Api\Data\SyncOrderItemInterface $syncOrderItemInterface
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Shippit\Shipping\Api\Data\SyncOrderItemInterface $syncOrderItemInterface,
        \Shippit\Shipping\Model\Sync\Order\ItemFactory $syncOrderItemFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->orderInterface = $orderInterface;
        $this->syncOrderItemInterface = $syncOrderItemInterface;
        $this->syncOrderItemFactory = $syncOrderItemFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Shippit\Shipping\Model\ResourceModel\Sync\Order');
    }

    public function addSyncOrderRequest(\Shippit\Shipping\Api\Request\SyncOrderInterface $syncOrderRequest)
    {
        return $this->setOrderId($syncOrderRequest->getOrderId())
            ->addItems($syncOrderRequest->getItems())
            ->setShippingMethod($syncOrderRequest->getShippingMethod())
            ->setApiKey($syncOrderRequest->getApiKey());
    }

    /**
     * Get the Sync Order Id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::SYNC_ORDER_ID);
    }

    /**
     * Set the Sync Order Id
     *
     * @param int|null $syncOrderId
     * @return self
     */
    public function setId($syncOrderId)
    {
        return $this->setData(self::SYNC_ORDER_ID, $syncOrderId);
    }

    /**
     * Get the Sync Order Id
     *
     * @return int|null
     */
    public function getSyncOrderId()
    {
        return $this->getData(self::SYNC_ORDER_ID);
    }

    /**
     * Set the Sync Order Id
     *
     * @param int|null $syncOrderId
     * @return self
     */
    public function setSyncOrderId($syncOrderId)
    {
        return $this->setData(self::SYNC_ORDER_ID, $syncOrderId);
    }

    /**
     * Get the API Key
     *
     * @return string|null
     */
    public function getApiKey()
    {
        return $this->getData(self::API_KEY);
    }

    /**
     * Set the API Key
     *
     * @param string|null $apiKey
     * @return self
     */
    public function setApiKey($apiKey)
    {
        return $this->setData(self::API_KEY, $apiKey);
    }

    /**
     * Populates the order details in the sync table
     *
     * @param object $order [description]
     * @return object $this;
     */
    public function addOrder($order)
    {
        $this->setOrderId($order->getId());
        $this->setShippingMethod('standard');
        $this->setStatus(self::STATUS_PENDING);

        return $this;
    }

    public function getOrder()
    {
        if (!$this->order instanceof $this->orderInterface) {
            $this->order = $this->orderInterface->load($this->getOrderId());
        }

        return $this->order;
    }

    /**
     * Get the Order Id
     *
     * @return int|null
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Set the Order Id
     *
     * @param int|null $orderId
     * @return self
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get the Shipping Method
     *
     * @return string|null
     */
    public function getShippingMethod()
    {
        return $this->getData(self::SHIPPING_METHOD);
    }

    /**
     * Set the Shipping Method
     *
     * @param string $shippingMethod
     * @return self
     */
    public function setShippingMethod($shippingMethod)
    {
        return $this->setData(self::SHIPPING_METHOD, $shippingMethod);
    }

    /**
     * Get the attempt count
     *
     * @return int|null
     */
    public function getAttemptCount()
    {
        return $this->getData(self::ATTEMPT_COUNT);
    }

    /**
     * Set the attempt count
     *
     * @param int|null $attemptCount
     * @return self
     */
    public function setAttemptCount($attemptCount)
    {
        return $this->setData(self::ATTEMPT_COUNT, $attemptCount);
    }

    /**
     * Get the status
     *
     * @return int|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set the status
     *
     * @param int $status
     * @return self
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get the tracking number
     *
     * @return string|null
     */
    public function getTrackingNumber()
    {
        return $this->getData(self::TRACKING_NUMBER);
    }

    /**
     * Set the tracking number
     *
     * @param string $trackingNumber
     * @return self
     */
    public function setTrackingNumber($trackingNumber)
    {
        return $this->setData(self::TRACKING_NUMBER, $trackingNumber);
    }

    /**
     * Retrieve sync order items collection
     *
     * @param   bool $useCache
     * @return  \Magento\Eav\Model\Entity\Collection\AbstractCollection;
     */
    public function getItemsCollection($useCache = true)
    {
        if ($this->itemsCollection === null || !$useCache) {
            $this->itemsCollection = $this->syncOrderItemInterface
                ->getCollection()
                ->addSyncOrderFilter($this);
        }

        return $this->itemsCollection;
    }

    /**
     * Retrieve the sync order items
     *
     * @return array
     */
    public function getItems()
    {
        $this->items = $this->getItemsCollection()->getItems();

        return $this->items;
    }

    /**
     * Add a new item to the sync order request
     *
     * @param \Shippit\Shipping\Api\Data\SyncOrderItemInterface $item
     */
    public function addItem(\Shippit\Shipping\Api\Data\SyncOrderItemInterface $item)
    {
        if (!$item->getSyncItemId()) {
            $this->getItemsCollection()->addItem($item);
            $this->items[] = $item;
        }

        return $this;
    }

    /**
     * Add new items to the sync order request
     *
     * @param Array $items
     */
    public function addItems(array $items)
    {
        foreach ($items as $item) {
            $itemObject = $this->syncOrderItemFactory
                ->create()
                ->addItem($item);

            $this->addItem($itemObject);
        }

        return $this;
    }
}
