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
 * @copyright  Copyright (c) 2017 by Shippit Pty Ltd (http://www.shippit.com)
 * @author     Matthew Muscat <matthew@mamis.com.au>
 * @license    http://www.shippit.com/terms
 */

namespace Shippit\Shipping\Api\Data;

interface OrderShippitMerchantItemInterface
{
    const ITEM_ID = 'item_id';
    const SKU = 'sku';
    const NAME = 'name';
    const QTY = 'qty';

    /**
     * Get the item id
     *
     * @return string|null
     */
    public function getItemId();

    /**
     * Set the item id
     *
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId);

    /**
     * Get the item sku
     *
     * @return string|null
     */
    public function getSku();

    /**
     * Set the item sku
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Get the item name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set the item name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get the item qty
     *
     * @return float|null
     */
    public function getQty();

    /**
     * Set the item qty
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);
}
