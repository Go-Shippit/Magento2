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

namespace Shippit\Shipping\Api\Data;

interface SyncOrderItemInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const SYNC_ITEM_ID          = 'sync_item_id';
    const SYNC_ORDER_ID         = 'sync_order_id';
    const SKU                   = 'sku';
    const TITLE                 = 'title';
    const QTY                   = 'qty';
    const PRICE                 = 'price';
    const WEIGHT                = 'weight';
    const LOCATION              = 'location';

    /**
     * Get the Sync Item Id
     *
     * @return string|null
     */
    public function getId();

    /**
     * Set the Sync Item Id
     *
     * @param integer $syncItemId
     * @return string|null
     */
    public function setId($syncItemId);

    /**
     * Get the Sync Item Id
     *
     * @return string|null
     */
    public function getSyncItemId();

    /**
     * Set the Sync Item Id
     *
     * @param integer $syncItemId
     * @return string|null
     */
    public function setSyncItemId($syncItemId);

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
     * Get the Item Sku
     *
     * @return string|null
     */
    public function getSku();

    /**
     * Set the Item Sku
     *
     * @param string $sku
     * @return string|null
     */
    public function setSku($sku);

    /**
     * Get the Item Title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Set the Item Title
     *
     * @param string $sku
     * @return string|null
     */
    public function setTitle($title);

    /**
     * Get the Item Qty
     *
     * @return string|null
     */
    public function getQty();

    /**
     * Set the Item Qty
     *
     * @param string $qty
     * @return string|null
     */
    public function setQty($qty);

    /**
     * Get the Item Price
     *
     * @return string|null
     */
    public function getPrice();

    /**
     * Set the Item Price
     *
     * @param string $sku
     * @return string|null
     */
    public function setPrice($price);

    /**
     * Get the Item Weight
     *
     * @return string|null
     */
    public function getWeight();

    /**
     * Set the Item Weight
     *
     * @param string $weight
     * @return string|null
     */
    public function setWeight($weight);

    /**
     * Get the Item Location
     *
     * @return string|null
     */
    public function getLocation();

    /**
     * Set the Item Location
     *
     * @param string $location
     * @return string|null
     */
    public function setLocation($location);

    /**
     * Add a new item to the sync order item request
     */
    public function addItem($item);
}
