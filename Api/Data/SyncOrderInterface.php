<?php
/**
 *  Shippit Pty Ltd
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the terms
 *  that is available through the world-wide-web at this URL:
 *  http://www.shippit.com/terms
 *
 *  @category   Shippit
 *  @copyright  Copyright (c) 2016 by Shippit Pty Ltd (http://www.shippit.com)
 *  @author     Matthew Muscat <matthew@mamis.com.au>
 *  @license    http://www.shippit.com/terms
 */

namespace Shippit\Shipping\Api\Data;

interface SyncOrderInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const SYNC_ORDER_ID         = 'sync_order_id';
    const STORE_ID              = 'store_id';
    const ORDER_ID              = 'order_id';
    const ATTEMPT_COUNT         = 'attempt_count';
    const STATUS                = 'status';
    const TRACKING_NUMBER       = 'tracking_number';
    const SYNCED_AT             = 'synced_at';

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
     * @return string|null
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
     * @return string|null
     */
    public function setSyncOrderId($syncOrderId);

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
     * @return string|null
     */
    public function setStoreId($storeId);

    /**
     * Get the Order Id
     *
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set the Order Id
     *
     * @param string $orderId
     * @return string|null
     */
    public function setOrderId($orderId);

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
     * @return string|null
     */
    public function setAttemptCount($attemptCount);

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
     * @return string|null
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
     * @return string|null
     */
    public function setTrackingNumber($trackingNumber);
}