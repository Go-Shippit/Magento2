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

interface QuoteInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ORDER_DATE            = 'order_date';
    const DEST_CITY             = 'dest_city';
    const DEST_POSTCODE         = 'dest_postcode';
    const DEST_REGION_CODE      = 'dest_region_code';
    const DROPOFF_STATE         = 'dropoff_state';
    const DROPOFF_ADDRESS       = 'dropoff_address';
    const PARCEL_ATTRIBUTES     = 'parcel_attributes';

    /**
     * Get the Order Date
     *
     * @return string|null
     */
    public function getOrderDate();

    /**
     * Set the Order Date
     *
     * @param DateTime|string $orderDate
     * @return string|null
     */
    public function setOrderDate($orderDate);

    /**
     * Get the Dest City
     *
     * @return string|null
     */
    public function getDestCity();

    /**
     * Set the Dest City
     *
     * @param string $destCity
     * @return string|null
     */
    public function setDestCity($destCity);

    /**
     * Get the Dest Postcode
     *
     * @return string|null
     */
    public function getDestPostcode();

    /**
     * Set the Dest Postcode
     *
     * @param string $destPostcode
     * @return string|null
     */
    public function setDestPostcode($destPostcode);

    /**
     * Get the Dest Region Code
     *
     * @return string|null
     */
    public function getDestRegionCode();

    /**
     * Set the Dest Region Code
     *
     * @param string $destRegionCode
     * @return string|null
     */
    public function setDestRegionCode($destRegionCode);

    /**
     * Get the Dropoff State
     *
     * @return string|null
     */
    public function getDropoffState();

    /**
     * Set the Dropoff State
     *
     * @param string $dropoffState
     * @return string|null
     */
    public function setDropoffState($dropoffState);

    /**
     * Get the Dropoff Address
     *
     * @return string|null
     */
    public function getDropoffAddress();

    /**
     * Set the Dropoff Address
     *
     * @param string $dropoffAddress
     * @return string|null
     */
    public function setDropoffAddress($dropoffAddress);

    /**
     * Get the Parcel Attributes
     *
     * @return string|null
     */
    public function getParcelAttributes();

    /**
     * Set the Parcel Attributes
     *
     * @param string $parcelAttributes
     * @return string|null
     */
    public function setParcelAttributes($parcelAttributes);
}
