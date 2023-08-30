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

interface SyncShipmentInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const SYNC_SHIPMENT_ID      = 'sync_shipment_id';
    const STORE_ID              = 'store_id';
    const ORDER_INCREMENT       = 'order_increment';
    const SHIPMENT_INCREMENT    = 'shipment_increment';
    const STATUS                = 'status';
    const COURIER_ALLOCATION    = 'courier_allocation';
    const TRACK_NUMBER          = 'track_number';
    const ATTEMPT_COUNT         = 'attempt_count';
    const CREATED_AT            = 'created_at';
    const SYNCED_AT             = 'synced_at';

    /**
     * Get the Sync Shipment Id
     *
     * @return string|null
     */
    public function getId();

    /**
     * Set the Sync Shipment Id
     *
     * @param integer $syncShipmentId
     * @return self
     */
    public function setId($syncShipmentId);

    /**
     * Get the Sync Shipment Id
     *
     * @return string|null
     */
    public function getSyncShipmentId();

    /**
     * Set the Sync Shipment Id
     *
     * @param integer $syncShipmentId
     * @return self
     */
    public function setSyncShipmentId($syncShipmentId);

    /**
     * Get the Store Id
     *
     * @return string|null
     */
    public function getStoreId();

    /**
     * Set the Store Id
     *
     * @param string $storeId
     * @return self
     */
    public function setStoreId($storeId);

    /**
     * Get the Order Increment
     *
     * @return string|null
     */
    public function getOrderIncrement();

    /**
     * Set the Order Increment
     *
     * @param string $orderIncrement
     * @return self
     */
    public function setOrderIncrement($orderIncrement);

    /**
     * Get the Shipment Increment
     *
     * @return string|null
     */
    public function getShipmentIncrement();

    /**
     * Set the Shipment Increment
     *
     * @param string $shipmentIncrement
     * @return self
     */
    public function setShipmentIncrement($shipmentIncrement);

    /**
     * Get the status
     *
     * @return string|null
     */
    public function getStatus();

    /**
     * Set the status
     *
     * @param string $status
     * @return self
     */
    public function setStatus($status);

    /**
     * Get the courier allocation
     *
     * @return string|null
     */
    public function getCourierAllocation();

    /**
     * Set the courier allocation
     *
     * @param string $courierAllocation
     * @return self
     */
    public function setCourierAllocation($courierAllocation);

    /**
     * Get the tracking number
     *
     * @return string|null
     */
    public function getTrackNumber();

    /**
     * Set the track number
     *
     * @param string $trackNumber
     * @return self
     */
    public function setTrackNumber($trackNumber);

    /**
     * Get the attempt count
     *
     * @return string|null
     */
    public function getAttemptCount();

    /**
     * Set the attempt count
     *
     * @param string $attemptCount
     * @return self
     */
    public function setAttemptCount($attemptCount);

    /**
     * Get the sync at
     *
     * @return string|null
     */
    public function getSyncedAt();

    /**
     * Set the sync at
     *
     * @param string|null $syncedAt
     * @return self
     */
    public function setSyncedAt($syncedAt);

    /**
     * get the created at
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * set the created at
     *
     * @param string|null $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt);

    /**
     * Retrieve sync shipment items collection
     *
     * @param   bool $useCache
     * @return  \Magento\Eav\Model\Entity\Collection\AbstractCollection;
     */
    public function getItemsCollection($useCache = true);

    /**
     * Add new items to the sync order request
     *
     * @param array $items
     * @return self
     */
    public function addItems(array $items);
}
