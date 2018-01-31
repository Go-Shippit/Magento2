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

namespace Shippit\Shipping\Model\Sync\Order;

class Item extends \Magento\Framework\Model\AbstractModel implements \Shippit\Shipping\Api\Data\SyncOrderItemInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Shippit\Shipping\Model\ResourceModel\Sync\Order\Item');
    }

    /**
     * Get the Sync Item Id
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getData(self::SYNC_ITEM_ID);
    }

    /**
     * Set the Sync Item Id
     *
     * @param integer $syncItemId
     * @return string|null
     */
    public function setId($syncItemId)
    {
        return $this->setData(self::SYNC_ITEM_ID, $syncItemId);
    }

    /**
     * Get the Sync Item Id
     *
     * @return string|null
     */
    public function getSyncItemId()
    {
        return $this->getId();
    }

    /**
     * Set the Sync Item Id
     *
     * @param integer $syncItemId
     * @return string|null
     */
    public function setSyncItemId($syncItemId)
    {
        return $this->setId($syncItemId);
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
     * @param integer $syncOrderId
     * @return string|null
     */
    public function setSyncOrderId($syncOrderId)
    {
        return $this->setData(self::SYNC_ORDER_ID, $syncOrderId);
    }

    /**
     * Get the Item Sku
     *
     * @return string|null
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * Set the Item Sku
     *
     * @param string $sku
     * @return string|null
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * Get the Item Title
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Set the Item Title
     *
     * @param string $sku
     * @return string|null
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Get the Item Qty
     *
     * @return string|null
     */
    public function getQty()
    {
        return $this->getData(self::QTY);
    }

    /**
     * Set the Item Qty
     *
     * @param string $qty
     * @return string|null
     */
    public function setQty($qty)
    {
        return $this->setData(self::QTY, $qty);
    }

    /**
     * Get the Item Price
     *
     * @return string|null
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * Set the Item Price
     *
     * @param string $sku
     * @return string|null
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * Get the Item Weight
     *
     * @return string|null
     */
    public function getWeight()
    {
        return $this->getData(self::WEIGHT);
    }

    /**
     * Set the Item Weight
     *
     * @param string $weight
     * @return string|null
     */
    public function setWeight($weight)
    {
        return $this->setData(self::WEIGHT, $weight);
    }

    /**
     * Get the Item Length
     *
     * @return string|null
     */
    public function getLength()
    {
        return $this->getData(self::LENGTH);
    }

    /**
     * Set the Item Length
     *
     * @param string $length
     * @return string|null
     */
    public function setLength($length)
    {
        return $this->setData(self::LENGTH, $length);
    }

    /**
     * Get the Item Width
     *
     * @return string|null
     */
    public function getWidth()
    {
        return $this->getData(self::WIDTH);
    }

    /**
     * Set the Item Width
     *
     * @param string $width
     * @return string|null
     */
    public function setWidth($width)
    {
        return $this->setData(self::WIDTH, $width);
    }

    /**
     * Get the Item Depth
     *
     * @return string|null
     */
    public function getDepth()
    {
        return $this->getData(self::DEPTH);
    }

    /**
     * Set the Item Depth
     *
     * @param string $depth
     * @return string|null
     */
    public function setDepth($depth)
    {
        return $this->setData(self::DEPTH, $depth);
    }

    /**
     * Get the Item Location
     *
     * @return string|null
     */
    public function getLocation()
    {
        return $this->getData(self::LOCATION);
    }

    /**
     * Set the Item Location
     *
     * @param string $location
     * @return string|null
     */
    public function setLocation($location)
    {
        return $this->setData(self::LOCATION, $location);
    }

    /**
     * Add a new item to the sync order item request
     */
    public function addItem($item)
    {
        return $this->setData($item);
    }
}
