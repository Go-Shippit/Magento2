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

interface SyncShipmentItemInterface
{

    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const SYNC_SHIPMENT_ITEM_ID     = 'sync_shipment_item_id';
    const SYNC_SHIPMENT_ID          = 'sync_shipment_id';
    const SKU                       = 'sku';
    const TITLE                     = 'title';
    const QTY                       = 'qty';
    const PRICE                     = 'price';
    const WEIGHT                    = 'weight';
    const LOCATION                  = 'location';

    /**
     * Get the Sync Item Id
     *
     * @return string|null
     */
    public function getId();

    /**
     * Set the Sync Shipment Item Id
     *
     * @param integer $syncShipmentItemId
     * @return string|null
     */
    public function setId($syncShipmentItemId);

    /**
     * Get the Sync Shipment Item Id
     *
     * @return string|null
     */
    public function getSyncShipmentItemId();

    /**
     * Set the Sync Item Id
     *
     * @param integer $syncShipmentItemId
     * @return string|null
     */
    public function setSyncShipmentItemId($syncShipmentItemId);

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
     * @return string|null
     */
    public function setSyncShipmentId($syncShipmentId);

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
