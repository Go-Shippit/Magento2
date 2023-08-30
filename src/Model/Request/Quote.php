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

namespace Shippit\Shipping\Model\Request;

use Shippit\Shipping\Api\Request\QuoteInterface;

class Quote extends \Magento\Framework\Model\AbstractModel implements QuoteInterface
{
    /**
     * @var \Shippit\Shipping\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Shippit\Shipping\Helper\Data $helper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Shippit\Shipping\Helper\Data $helper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Get the Order Date
     *
     * @return string|null
     */
    public function getOrderDate()
    {
        return $this->getData(self::ORDER_DATE);
    }

    /**
     * Set the Order Date
     *
     * @param string|null $orderDate
     * @return self
     */
    public function setOrderDate($orderDate)
    {
        return $this->setData(self::ORDER_DATE, $orderDate);
    }

    /**
     * Get the Dropoff Address
     *
     * @return string|null
     */
    public function getDropoffAddress()
    {
        return $this->getData(self::DROPOFF_ADDRESS);
    }

    /**
     * Set the Dropoff Address
     *
     * @param string|null $dropoffAddress
     * @return self
     */
    public function setDropoffAddress($dropoffAddress)
    {
        return $this->setData(self::DROPOFF_ADDRESS, $dropoffAddress);
    }

    /**
     * Get the Dest City
     *
     * @return string|null
     */
    public function getDropoffSuburb()
    {
        return $this->getData(self::DROPOFF_SUBURB);
    }

    /**
     * Set the Dest City
     *
     * @param string|null $dropoffSuburb
     * @return self
     */
    public function setDropoffSuburb($dropoffSuburb)
    {
        return $this->setData(self::DROPOFF_SUBURB, $dropoffSuburb);
    }

    /**
     * Get the Dropoff Postcode
     *
     * @return string|null
     */
    public function getDropoffPostcode()
    {
        return $this->getData(self::DROPOFF_POSTCODE);
    }

    /**
     * Set the Dropoff Postcode
     *
     * @param string|null $dropoffPostcode
     * @return self
     */
    public function setDropoffPostcode($dropoffPostcode)
    {
        return $this->setData(self::DROPOFF_POSTCODE, $dropoffPostcode);
    }

    /**
     * Get the Dropoff State
     *
     * @return string|null
     */
    public function getDropoffState()
    {
        return $this->getData(self::DROPOFF_STATE);
    }

    /**
     * Set the Dropoff Country Code
     *
     * @param string|null $dropoffCountryCode
     * @return self
     */
    public function setDropoffState($dropoffCountryCode)
    {
        return $this->setData(self::DROPOFF_STATE, $dropoffCountryCode);
    }

    /**
     * Get the Dropoff Postcode
     *
     * @return string|null
     */
    public function getDropoffCountryCode()
    {
        return $this->getData(self::DROPOFF_COUNTRY_CODE);
    }

    /**
     * Set the Dropoff State
     *
     * @param string|null $dropoffCountryCode
     * @return self
     */
    public function setDropoffCountryCode($dropoffCountryCode)
    {
        return $this->getData(self::DROPOFF_COUNTRY_CODE, $dropoffCountryCode);
    }

    /**
     * Get the Parcel Attributes
     *
     * @return string|null
     */
    public function getParcelAttributes()
    {
        return $this->getData(self::PARCEL_ATTRIBUTES);
    }

    /**
     * Set the Parcel Attributes
     *
     * @param string $parcelAttributes
     * @return self
     */
    public function setParcelAttributes($parcelAttributes)
    {
        return $this->setData(self::PARCEL_ATTRIBUTES, $parcelAttributes);
    }

    /**
     * Get the Dutiable Amount
     *
     * @return float|null
     */
    public function getDutiableAmount()
    {
        return $this->getData(self::DUTIABLE_AMOUNT);
    }

    /**
     * Set the Dutiable Amount
     *
     * @param float $dutiableAmount
     * @return self
     */
    public function setDutiableAmount($dutiableAmount)
    {
        return $this->setData(self::DUTIABLE_AMOUNT, $dutiableAmount);
    }
}
