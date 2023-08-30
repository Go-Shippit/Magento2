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

namespace Shippit\Shipping\Api\Data;

interface SyncOrderInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const SYNC_ORDER_ID         = 'sync_order_id';
    const API_KEY               = 'api_key';
    const ORDER_ID              = 'order_id';
    const SHIPPING_METHOD       = 'shipping_method';
    const ATTEMPT_COUNT         = 'attempt_count';
    const STATUS                = 'status';
    const TRACKING_NUMBER       = 'tracking_number';
    const SYNCED_AT             = 'synced_at';

    /**
     * Add the Sync Order Request
     */
    public function addSyncOrderRequest(\Shippit\Shipping\Api\Request\SyncOrderInterface $syncOrderRequest);

    /**
     * Get the Sync Order Id
     *
     * @return string|null
     */
    public function getId();

    /**
     * Set the Sync Order Id
     *
     * @param integer $syncOrderId
     * @return self
     */
    public function setId($syncOrderId);

    /**
     * Get the Sync Order Id
     *
     * @return string|null
     */
    public function getSyncOrderId();

    /**
     * Set the Sync Order Id
     *
     * @param integer $syncOrderId
     * @return self
     */
    public function setSyncOrderId($syncOrderId);

    /**
     * Get the API Key
     *
     * @return string|null
     */
    public function getApiKey();

    /**
     * Set the API Key
     *
     * @param string $apiKey
     * @return self
     */
    public function setApiKey($apiKey);

    /**
     * Get the Order Id
     *
     * @return int|null
     */
    public function getOrderId();

    /**
     * Set the Order Id
     *
     * @param int|null $orderId
     * @return self
     */
    public function setOrderId($orderId);

    /**
     * Get the Shipping Method
     *
     * @return string|null
     */
    public function getShippingMethod();

    /**
     * Set the Shipping Method
     *
     * @param string $shippingMethod
     * @return self
     */
    public function setShippingMethod($shippingMethod);

    /**
     * Get the attempt count
     *
     * @return int|null
     */
    public function getAttemptCount();

    /**
     * Set the attempt count
     *
     * @param int $attemptCount
     * @return self
     */
    public function setAttemptCount($attemptCount);

    /**
     * Get the status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set the status
     *
     * @param int $status
     * @return self
     */
    public function setStatus($status);

    /**
     * Get the tracking number
     *
     * @return string|null
     */
    public function getTrackingNumber();

    /**
     * Set the tracking number
     *
     * @param string $trackingNumber
     * @return self
     */
    public function setTrackingNumber($trackingNumber);

    /**
     * Retrieve sync order items collection
     *
     * @param   bool $useCache
     * @return  \Magento\Eav\Model\Entity\Collection\AbstractCollection;
     */
    public function getItemsCollection($useCache = true);

    /**
     * Retrieve the sync order items
     *
     * @return array
     */
    public function getItems();

    /**
     * Add a new item to the sync order request
     *
     * @param \Shippit\Shipping\Api\Data\SyncOrderItemInterface $item
     */
    public function addItem(\Shippit\Shipping\Api\Data\SyncOrderItemInterface $item);

    /**
     * Add new items to the sync order request
     *
     * @param Array $items
     */
    public function addItems(array $items);
}
