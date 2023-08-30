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
    const LENGTH                = 'length';
    const WIDTH                 = 'width';
    const DEPTH                 = 'depth';
    const LOCATION              = 'location';
    const TARIFF_CODE           = 'tariff_code';
    const ORIGIN_COUNTRY_CODE   = 'origin_country_code';
    const DANGEROUS_GOODS_CODE   = 'dangerous_goods_code';
    const DANGEROUS_GOODS_TEXT   = 'dangerous_goods_text';

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
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
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
     * @param string $title
     * @return self
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
     * @return self
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
     * @param string $price
     * @return self
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
     * @return self
     */
    public function setWeight($weight);

    /**
     * Get the Item Length
     *
     * @return string|null
     */
    public function getLength();

    /**
     * Set the Item Length
     *
     * @param string $length
     * @return self
     */
    public function setLength($length);

    /**
     * Get the Item Width
     *
     * @return string|null
     */
    public function getWidth();

    /**
     * Set the Item Width
     *
     * @param string $width
     * @return self
     */
    public function setWidth($width);

    /**
     * Get the Item Depth
     *
     * @return string|null
     */
    public function getDepth();

    /**
     * Set the Item Depth
     *
     * @param string $depth
     * @return self
     */
    public function setDepth($depth);

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
     * @return self
     */
    public function setLocation($location);

    /**
     * Get the Item Tariffcode
     *
     * @return string|null
     */
    public function getTariffCode();

    /**
     * Set the Item Tariffcode
     *
     * @param string $tariffCode
     * @return self
     */
    public function setTariffCode($tariffCode);

    /**
     * Get the Item Origin Country Code
     *
     * @return string|null
     */
    public function getOriginCountryCode();

    /**
     * Set the Item Origin Country Code
     *
     * @param string $originCountryCode
     * @return self
     */
    public function setOriginCountryCode($originCountryCode);

    /**
     * Set the Item Dangerous Goods Code
     *
     * @param string $dangerousGoodsCode
     * @return self
     */
    public function setDangerousGoodsCode($dangerousGoodsCode);

    /**
     * Get the Item Dangerous Goods Code
     *
     * @return string|null
     */
    public function getDangerousGoodsCode();

    /**
     * Set the Item Dangerous Goods Text
     *
     * @param string $dangerousGoodsText
     * @return self
     */
    public function setDangerousGoodsText($dangerousGoodsText);

    /**
     * Get the Item Dangerous Goods Text
     *
     * @return string|null
     */
    public function getDangerousGoodsText();

    /**
     * Add a new item to the sync order item request
     *
     * @param array $item
     * @return self
     */
    public function addItem($item);
}
