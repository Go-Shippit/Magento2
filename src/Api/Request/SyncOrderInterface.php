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

interface SyncOrderInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const API_KEY               = 'api_key';
    const ORDER_ID              = 'order_id';
    const ORDER                 = 'order';
    const SHIPPING_METHOD       = 'shipping_method';
    const ITEMS                 = 'items';

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
     * Get the Order Object
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     */
    public function getOrder();

    /**
     * Set the Order Object
     *
     * @param \Magento\Sales\Api\Data\OrderInterface|null $order
     * @return self
     */
    public function setOrder($order);

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
     * @return \Shippit\Shipping\Api\Request\SyncOrderInterface
     */
    public function setShippingMethod($shippingMethod);

    /**
     * Retrieve the sync order items
     *
     * @return array
     */
    public function getItems();

    /**
     * Set the sync order items
     *
     * @return \Shippit\Shipping\Api\Request\SyncOrderInterface
     */
    public function setItems();

    /**
     * Reset this instance of data to default values
     *
     * @return \Shippit\Shipping\Api\Request\SyncOrderInterface
     */
    public function reset();
}
