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

interface OrderInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const RETAILER_INVOICE          = 'retailer_invoice';
    const AUTHORITY_TO_LEAVE        = 'authority_to_leave';
    const DELIVERY_INSTRUCTIONS     = 'delivery_instructions';
    const USER_ATTRIBUTES           = 'user_attributes';
    const COURIER_TYPE              = 'courier_type';
    const COURIER_ALLOCATION        = 'courier_allocation';
    const DELIVERY_DATE             = 'delivery_date';
    const DELIVERY_WINDOW           = 'delivery_window';
    const RECEIVER_NAME             = 'receiver_name';
    const RECEIVER_CONTACT_NUMBER   = 'receiver_contact_number';
    const DELIVERY_COMPANY          = 'delivery_company';
    const DELIVERY_ADDRESS          = 'delivery_address';
    const DELIVERY_SUBURB           = 'delivery_suburb';
    const DELIVERY_POSTCODE         = 'delivery_postcode';
    const DELIVERY_STATE            = 'delivery_state';
    const DELIVERY_COUNTRY          = 'delivery_country_code';
    const PARCEL_ATTRIBUTES         = 'parcel_attributes';
    const SOURCE_PLATFORM           = 'source_platform';
    const PRODUCT_CURRENCY          = 'product_currency';

    /**
     * Set the order to be sent to the api request
     *
     * @param object $order The Order Request
     */
    public function setOrder($order);

    /**
     * Add an item to the parcel api request
     * @param string  $sku      The product sku
     * @param string  $title    The product name
     * @param integer $qty      The product qty
     * @param float   $price    The product price
     * @param integer $weight   The product weight
     * @param string  $location The product location
     */
    public function addItem($sku, $title, $qty, $price, $weight = 0, $location = null);

    /**
     * Get the Retailer Invoice Referance
     *
     * @return string|null
     */
    public function getRetailerInvoice();

    /**
     * Set the Retailer Invoice Referance
     *
     * @param string $orderDate
     * @return string
     */
    public function setRetailerInvoice($retailerInvoice);

    /**
     * Get the Authority To Leave
     *
     * @return bool|null
     */
    public function getAuthorityToLeave();

    /**
     * Set the Authority To Leave
     *
     * @param bool $authorityToLeave
     * @return bool
     */
    public function setAuthorityToLeave($authorityToLeave);

    /**
     * Get the Delivery Instructions
     *
     * @return string|null
     */
    public function getDeliveryInstructions();

    /**
     * Set the Delivery Instructions
     *
     * @param string $deliveryInstructions
     * @return string
     */
    public function setDeliveryInstructions($deliveryInstructions);

    /**
     * Get the User Attributes
     *
     * @return array|null
     */
    public function getUserAttributes();

    /**
     * Set the User Attributes
     *
     * @param array $userAttributes
     * @return array
     */
    public function setUserAttributes($email, $firstname, $lastname);

    /**
     * Set the Courier Type
     *
     * @param array $courierType
     * @return array
     */
    public function setCourierType($courierType);

    /**
     * Get the Courier Type
     *
     * @return array|null
     */
    public function getCourierType();

    /**
     * Set the Courier Allocation
     *
     * @param array $courierAllocation
     * @return array
     */
    public function setCourierAllocation($courierAllocation);

    /**
     * Get the Courier Allocation
     *
     * @return array|null
     */
    public function getCourierAllocation();

    /**
     * Set the Delivery Date
     *
     * @param array $deliveryDate
     * @return array
     */
    public function setDeliveryDate($deliveryDate);

    /**
     * Get the Delivery Date
     *
     * @return array|null
     */
    public function getDeliveryDate();

    /**
     * Set the Delivery Window
     *
     * @param array $deliveryWindow
     * @return array
     */
    public function setDeliveryWindow($deliveryWindow);

    /**
     * Get the Delivery Window
     *
     * @return string|null
     */
    public function getDeliveryWindow();

    /**
     * Set the Reciever Name
     *
     * @param string $receiverName    Receiver Name
     * @return string
     */
    public function setReceiverName($receiverName);

    /**
     * Get the Receiver Contact Number
     *
     * @return string|null
     */
    public function getReceiverContactNumber();

    /**
     * Set the Reciever Contact Number
     *
     * @param string $receiverContactNumber    Receiver Contact Number
     * @return string
     */
    public function setReceiverContactNumber($receiverContactNumber);

    /**
     * Get the Delivery Company
     *
     * @return string|null
     */
    public function getDeliveryCompany();

    /**
     * Set the Delivery Company
     *
     * @param string $deliveryCompany   Delivery Company
     * @return string
     */
    public function setDeliveryCompany($deliveryCompany);

    /**
     * Get the Delivery Address
     *
     * @return string|null
     */
    public function getDeliveryAddress();

    /**
     * Set the Delivery Address
     *
     * @param string $deliveryAddress   Delivery Address
     * @return string
     */
    public function setDeliveryAddress($deliveryAddress);

    /**
     * Get the Delivery Suburb
     *
     * @return string|null
     */
    public function getDeliverySuburb();

    /**
     * Set the Delivery Suburb
     *
     * @param string $deliverySuburb   Delivery Suburb
     * @return string
     */
    public function setDeliverySuburb($deliverySuburb);

    /**
     * Get the Delivery Postcode
     *
     * @return string|null
     */
    public function getDeliveryPostcode();

    /**
     * Set the Delivery Postcode
     *
     * @param string $deliveryPostcode   Delivery Postcode
     * @return string
     */
    public function setDeliveryPostcode($deliveryPostcode);

    /**
     * Get the Delivery State
     *
     * @return string|null
     */
    public function getDeliveryState();

    /**
     * Set the Delivery State
     *
     * @param string $deliveryState   Delivery State
     * @return string
     */
    public function setDeliveryState($deliveryState);

    /**
     * Get the Delivery Country
     *
     * @return string|null
     */
    public function getDeliveryCountry();

    /**
     * Set the Delivery Country
     *
     * @param string $deliveryCountry   Delivery Country
     * @return string
     */
    public function setDeliveryCountry($deliveryCountry);

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

    /**
     * Get the Source Platform
     *
     * @return string|null
     */
    public function getSourcePlatform();

    /**
     * Set the Source Platform
     *
     * @param string $sourcePlatform
     * @return string
     */
    public function setSourcePlatform($sourcePlatform);

    /**
     * Get the Product Currency
     *
     * @return string|null
     */
    public function getProductCurrency();

    /**
     * Set the Product Currency
     *
     * @param string $productCurrency
     * @return string
     */
    public function setProductCurrency($productCurrency);
}
