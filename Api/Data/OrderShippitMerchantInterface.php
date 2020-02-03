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

use Shippit\Shipping\Api\Data\OrderShippitMerchantAddressInterface;
use Shippit\Shipping\Api\Data\OrderShippitMerchantItemInterface;

interface OrderShippitMerchantInterface
{
    const MERCHANT_ID = 'merchant_id';
    const ADDRESS = 'address';
    const ITEMS = 'items';

    /**
     * Get the merchant id
     *
     * @return int|null
     */
    public function getMerchantId();

    /**
     * Set the merchant id
     *
     * @param int $merchantId
     * @return $this
     */
    public function setMerchantId($merchantId);

    /**
     * Get the merchant address
     *
     * @return \Shippit\Shipping\Api\Data\OrderShippitMerchantAddressInterface|null
     */
    public function getAddress();

    /**
     * Set the merchant address
     *
     * @param \Shippit\Shipping\Api\Data\OrderShippitMerchantAddressInterface $address
     * @return $this
     */
    public function setAddress(OrderShippitMerchantAddressInterface $address);

    /**
     * Get the merchant items
     *
     * @return \Shippit\Shipping\Api\Data\OrderShippitMerchantItemInterface[]|null
     */
    public function getItems();

    /**
     * Set the merchant items
     *
     * @param \Shippit\Shipping\Api\Data\OrderShippitMerchantItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * Add the merchant item
     *
     * @param \Shippit\Shipping\Api\Data\OrderShippitMerchantItemInterface $item
     * @return $this
     */
    public function addItem(OrderShippitMerchantItemInterface $item);
}
