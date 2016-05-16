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
    protected $_orderInterface;

    /**
     * An instance of an order
     *
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $_order;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Api\Data\OrderInterface $orderInterface
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_orderInterface = $orderInterface;

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
    
    /**
     * Get the Sync Order Id
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getData(self::SYNC_ORDER_ID);
    }

    /**
     * Set the Sync Order Id
     *
     * @param DateTime|string $orderDate
     * @return string|null
     */
    public function setId($syncOrderId)
    {
        return $this->setData(self::SYNC_ORDER_ID, $syncOrderId);
    }

    /**
     * Get the Sync Order Id
     *
     * @return string|null
     */
    public function getSyncOrderId()
    {
        return $this->getData(self::SYNC_ORDER_ID);
    }

    /**
     * Set the Sync Order Id
     *
     * @param DateTime|string $orderDate
     * @return string|null
     */
    public function setSyncOrderId($syncOrderId)
    {
        return $this->setData(self::SYNC_ORDER_ID, $syncOrderId);
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
        if (!$this->_order instanceof $this->_orderInterface) {
            $this->_order = $this->_orderInterface->load($this->getOrderId());
        }

        return $this->_order;
    }

    /**
     * Get the Order Id
     *
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Set the Order Id
     *
     * @param string $orderId
     * @return string|null
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
     * @return string|null
     */
    public function setShippingMethod($shippingMethod)
    {
        return $this->setData(self::SHIPPING_METHOD, $shippingMethod);
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
     * @return string|null
     */
    public function setTrackingNumber($trackingNumber)
    {
        return $this->setData(self::TRACKING_NUMBER, $trackingNumber);
    }
}