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

interface OrderShippitReturnAddressInterface
{
    const FIRSTNAME = 'firstname';
    const MIDDLENAME = 'middlename';
    const LASTNAME = 'lastname';
    const COMPANY = 'company';
    const PHONE = 'phone';
    const STREET = 'street';
    const SUBURB = 'suburb';
    const POSTCODE = 'postcode';
    const REGION = 'region';
    const REGION_CODE = 'region_code';
    const COUNTRY = 'country';
    const COUNTRY_CODE = 'country_code';

    /**
     * Get the firstname for the return address
     *
     * @return string|null
     */
    public function getFirstname();

    /**
     * Set the firstname for the return address
     *
     * @param string $firstname
     * @return string|null
     */
    public function setFirstname($firstname);

    /**
     * Get the middlename for the return address
     *
     * @return string|null
     */
    public function getMiddlename();

    /**
     * Set the middlename for the return address
     *
     * @param string $middlename
     * @return string|null
     */
    public function setMiddlename($middlename);

    /**
     * Get the lastname for the return address
     *
     * @return string|null
     */
    public function getLastname();

    /**
     * Set the lastname for the return address
     *
     * @param string $lastname
     * @return string|null
     */
    public function setLastname($lastname);

    /**
     * Get the company for the return address
     *
     * @return string|null
     */
    public function getCompany();

    /**
     * Set the company for the return address
     *
     * @param string $company
     * @return string|null
     */
    public function setCompany($company);

    /**
     * Get the phone for the return address
     *
     * @return string|null
     */
    public function getPhone();

    /**
     * Set the phone for the return address
     *
     * @param string $phone
     * @return string|null
     */
    public function setPhone($phone);

    /**
     * Get the street for the return address
     *
     * @return array|null
     */
    public function getStreet();

    /**
     * Set the street for the return address
     *
     * @param array|string $street
     * @return array|null
     */
    public function setStreet($street);

    /**
     * Get the suburb for the return address
     *
     * @return string|null
     */
    public function getSuburb();

    /**
     * Set the suburb for the return address
     *
     * @param string $suburb
     * @return string|null
     */
    public function setSuburb($suburb);

    /**
     * Get the postcode for the return address
     *
     * @return string|null
     */
    public function getPostcode();

    /**
     * Set the postcode for the return address
     *
     * @param string $postcode
     * @return string|null
     */
    public function setPostcode($postcode);

    /**
     * Get the region for the return address
     *
     * @return string|null
     */
    public function getRegion();

    /**
     * Set the region for the return address
     *
     * @param string $region
     * @return string|null
     */
    public function setRegion($region);

    /**
     * Get the region code for the return address
     *
     * @return string|null
     */
    public function getRegionCode();

    /**
     * Set the region code for the return address
     *
     * @param string $regionCode
     * @return string|null
     */
    public function setRegionCode($regionCode);

    /**
     * Get the country for the return address
     *
     * @return string|null
     */
    public function getCountry();

    /**
     * Set the country for the return address
     *
     * @param string $country
     * @return string|null
     */
    public function setCountry($country);

    /**
     * Get the country code for the return address
     *
     * @return string|null
     */
    public function getCountryCode();

    /**
     * Set the country code for the return address
     *
     * @param string $countryCode
     * @return string|null
     */
    public function setCountryCode($countryCode);
}
