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
    const RECEIVER_LANGUAGE_CODE    = 'receiver_language_code';

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
     * @param string $retailerInvoice
     * @return self
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
     * @param bool|null $authorityToLeave
     * @return self
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
     * @return self
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
     * @param string|null $email
     * @param string|null $firstname
     * @param string|null $lastname
     * @return self
     */
    public function setUserAttributes($email, $firstname, $lastname);

    /**
     * Get the Courier Type
     *
     * @return string|null
     */
    public function getCourierType();

    /**
     * Set the Courier Type
     *
     * @param string|null $courierType
     * @return self
     */
    public function setCourierType($courierType);

    /**
     * Get the Courier Allocation
     *
     * @return string|null
     */
    public function getCourierAllocation();

    /**
     * Set the Courier Allocation
     *
     * @param string|null $courierAllocation
     * @return self
     */
    public function setCourierAllocation($courierAllocation);

    /**
     * Get the Delivery Date
     *
     * @return string|null
     */
    public function getDeliveryDate();

    /**
     * Set the Delivery Date
     *
     * @param string $deliveryDate
     * @return self
     */
    public function setDeliveryDate($deliveryDate);

    /**
     * Get the Delivery Window
     *
     * @return string|null
     */
    public function getDeliveryWindow();

    /**
     * Set the Delivery Window
     *
     * @param string|null $deliveryWindow
     * @return self
     */
    public function setDeliveryWindow($deliveryWindow);

    /**
     * Set the Reciever Name
     *
     * @param string $receiverName    Receiver Name
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
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
     * @param string|null $deliverySuburb   Delivery Suburb
     * @return self
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
     * @param string|null $deliveryPostcode   Delivery Postcode
     * @return self
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
     * @param string|null $deliveryState   Delivery State
     * @return self
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
     * @param string|null $deliveryCountry   Delivery Country
     * @return self
     */
    public function setDeliveryCountry($deliveryCountry);

    /**
     * Get the Parcel Attributes
     *
     * @return array|null
     */
    public function getParcelAttributes();

    /**
     * Set the Parcel Attributes
     *
     * @param array|null $parcelAttributes
     * @return self
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
     * @return self
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
     * @return self
     */
    public function setProductCurrency($productCurrency);

    /**
     * Get the Receiver Language Code
     *
     * @return string|null
     */
    public function getReceiverLanguageCode();

    /**
     * Set the Receiver Language Code
     *
     * @param string|null $receiverLanguageCode
     * @return self
     */
    public function setReceiverLanguageCode($receiverLanguageCode);
}
