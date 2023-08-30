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

namespace Shippit\Shipping\Api\Request;

interface ShipmentInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case
     */
    const ORDER = 'order';
    const ITEMS = 'items';

    const ERROR_ORDER_MISSING = 'The order id requested was not found';
    const ERROR_ORDER_STATUS = 'The order id requested has an status that is not available for shipping';

    /**
     * Get the Order Id
     *
     * @return int|null
     */
    public function getOrderId();

    /**
     * Get the Order Object
     *
     * @return \Magento\Sales\Model\Order|null
     */
    public function getOrder();

    /**
     * Set the Order Object
     *
     * @param \Magento\Sales\Model\Order $order
     * @return self
     */
    public function setOrder($order);

    /**
     * Set the order id by the order increment
     *
     * @param string $incrementId
     * @return self
     */
    public function setOrderByIncrementId($incrementId);

    /**
     * Process items in the shipment request,
     * - ensures only items contained in the order are present
     * - ensures only qtys available for shipping are used in the shipment
     *
     * @param array $items The items to be included in the request
     * @return self
     */
    public function processItems($items = []);

    /**
     * Get the Items in the request
     *
     * @return array|null
     */
    public function getItems();

    /**
     * Set the items in the request
     *
     * @param array $items The items to be included in the request
     * @return self
     */
    public function setItems($items);

    /**
     * Add the item in the request
     *
     * @param string $itemId The Item Id to be Shipped
     * @param float $qty     The Item Qty to be Shipped
     * @return self
     */
    public function addItem($itemId, $qty);
}
