<?php
/**
 *  Shippit Pty Ltd
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the terms
 *  that is available through the world-wide-web at this URL:
 *  http://www.shippit.com/terms
 *
 *  @category   Shippit
 *  @copyright  Copyright (c) 2016 by Shippit Pty Ltd (http://www.shippit.com)
 *  @author     Matthew Muscat <matthew@mamis.com.au>
 *  @license    http://www.shippit.com/terms
 */

namespace Shippit\Shipping\Model\Request;

use Shippit\Shipping\Api\Request\QuoteInterface;

class Quote extends \Magento\Framework\Model\AbstractModel implements QuoteInterface
{
    /**
     * @param \Shippit\Shipping\Helper\Data $helper
     */
    public function __construct(
        \Shippit\Shipping\Helper\Data $helper
    ) {
        $this->_helper = $helper;
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
     * @param DateTime|string $orderDate
     * @return string|null
     */
    public function setOrderDate($orderDate)
    {
        return $this->setData(self::ORDER_DATE, $orderDate);
    }

    /**
     * Get the Dest City
     *
     * @return string|null
     */
    public function getDestCity()
    {
        return $this->getData(self::DEST_CITY);
    }

    /**
     * Set the Dest City
     *
     * @param string $destCity
     * @return string|null
     */
    public function setDestCity($destCity)
    {
        return $this->setData(self::DEST_CITY, $destCity);
    }

    /**
     * Get the Dest Postcode
     *
     * @return string|null
     */
    public function getDestPostcode()
    {
        return $this->getData(self::DEST_POSTCODE);
    }

    /**
     * Set the Dest Postcode
     *
     * @param string $destPostcode
     * @return string|null
     */
    public function setDestPostcode($destPostcode)
    {
        return $this->setData(self::DEST_POSTCODE, $destPostcode);
    }

    /**
     * Get the Dest Region Code
     *
     * @return string|null
     */
    public function getDestRegionCode()
    {
        return $this->getData(self::DEST_REGION_CODE);
    }

    /**
     * Set the Dest Region Code
     *
     * @param string $destRegionCode
     * @return string|null
     */
    public function setDestRegionCode($destRegionCode)
    {
        return $this->setData(self::DEST_REGION_CODE, $destRegionCode);
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
     * Set the Dropoff State
     *
     * @param string $dropoffState
     * @return string|null
     */
    public function setDropoffState($dropoffState)
    {
        if (empty($dropoffState)) {
            $dropoffState = $this->_helper->getRegionCodeFromPostcode($this->getDestPostcode());
        }

        return $this->setData(self::DROPOFF_STATE, $dropoffState);
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
     * @return string|null
     */
    public function setParcelAttributes($parcelAttributes)
    {
        return $this->setData(self::PARCEL_ATTRIBUTES, $parcelAttributes);
    }
}