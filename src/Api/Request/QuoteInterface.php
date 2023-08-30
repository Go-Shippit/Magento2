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
    const DROPOFF_ADDRESS       = 'dropoff_address';
    const DROPOFF_SUBURB        = 'dropoff_suburb';
    const DROPOFF_POSTCODE      = 'dropoff_postcode';
    const DROPOFF_STATE         = 'dropoff_state';
    const DROPOFF_COUNTRY_CODE  = 'dropoff_country_code';
    const PARCEL_ATTRIBUTES     = 'parcel_attributes';
    const DUTIABLE_AMOUNT       = 'dutiable_amount';

    /**
     * Get the Order Date
     *
     * @return string|null
     */
    public function getOrderDate();

    /**
     * Set the Order Date
     *
     * @param string|null $orderDate
     * @return self
     */
    public function setOrderDate($orderDate);

    /**
     * Get the Dropoff Address
     *
     * @return string|null
     */
    public function getDropoffAddress();

    /**
     * Set the Dropoff Address
     *
     * @param string|null $dropoffAddress
     * @return self
     */
    public function setDropoffAddress($dropoffAddress);

    /**
     * Get the Dropoff Suburb
     *
     * @return string|null
     */
    public function getDropoffSuburb();

    /**
     * Set the Dropoff Suburb
     *
     * @param string|null $dropoffSuburb
     * @return self
     */
    public function setDropoffSuburb($dropoffSuburb);

    /**
     * Get the Dropoff Postcode
     *
     * @return string|null
     */
    public function getDropoffPostcode();

    /**
     * Set the Dropoff Postcode
     *
     * @param string|null $dropoffPostcode
     * @return self
     */
    public function setDropoffPostcode($dropoffPostcode);

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
     * @return self
     */
    public function setDropoffState($dropoffState);

    /**
     * Get the Dropoff Country Code
     *
     * @return string|null
     */
    public function getDropoffCountryCode();

    /**
     * Set the Dropoff Country Code
     *
     * @param string|null $dropoffCountryCode
     * @return self
     */
    public function setDropoffCountryCode($dropoffCountryCode);

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
     * @return self
     */
    public function setParcelAttributes($parcelAttributes);

    /**
     * Get the Dutiable Amount
     *
     * @return float|null
     */
    public function getDutiableAmount();

    /**
     * Set the Dutiable Amount
     *
     * @param float $dutiableAmount
     * @return self
     */
    public function setDutiableAmount($dutiableAmount);
}
