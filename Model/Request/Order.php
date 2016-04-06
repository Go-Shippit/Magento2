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

use Shippit\Shipping\Api\Request\OrderInterface;

class Order extends \Magento\Framework\Model\AbstractModel implements OrderInterface
{
    protected $helper;
    protected $carrierCode;
    protected $order;

    // Shippit Service Class API Mappings
    const SHIPPING_SERVICE_STANDARD = 'CouriersPlease';
    const SHIPPING_SERVICE_EXPRESS  = 'eparcelexpress';
    const SHIPPING_SERVICE_PREMIUM  = 'Bonds';

    /**
     * @param \Shippit\Shipping\Helper\Data $helper
     */
    public function __construct(
        \Shippit\Shipping\Helper\Data $helper
    ) {
        $this->helper = $helper;
        
        // @TODO: get the carrier code from a central helper
        $this->carrierCode = 'shippit';
    }

    public function processSyncOrder(\Shippit\Shipping\Model\Sync
        \Order $syncOrder)
    {
        // get the order attached to the syncOrder object
        $order = $syncOrder->getOrder();
        // get the shipping method attached to the syncOrder object
        $shippingMethod = $syncOrder->getShippingMethod();

        // Build the order request
        $orderRequest = $this->setOrder($order)
            ->addItems()
            ->setShippingMethod($shippingMethod);

        return $this;
    }

    /**
     * Set the order to be sent to the api request
     *
     * @param object $order The Order Request
     */
    public function setOrder($order)
    {
        if ($order instanceof \Magento\Sales\Model\Order) {
            $this->order = $order;
        }
        else {
            $this->order = $this->load($order);
        }

        $billingAddress = $this->order->getBillingAddress();
        $shippingAddress = $this->order->getShippingAddress();

        $this->setRetailerInvoice($this->order->getIncrementId())
            ->setAuthorityToLeave($this->order->getShippitAuthorityToLeave())
            ->setDeliveryInstructions($this->order->getShippitDeliveryInstructions())
            ->setUserAttributes($billingAddress->getEmail(), $billingAddress->getFirstname(), $billingAddress->getLastname())
            ->setReceiverName($shippingAddress->getName())
            ->setReceiverContactNumber($shippingAddress->getTelephone())
            ->setDeliveryAddress(implode(' ', $shippingAddress->getStreet()))
            ->setDeliverySuburb($shippingAddress->getCity())
            ->setDeliveryPostcode($shippingAddress->getPostcode())
            ->setDeliveryState($shippingAddress->getRegionCode());

        return $this;
    }

    /**
     * Add items from the order to the parcel details
     */
    public function addItems()
    {
        $items = $this->order->getAllItems();
        //     array(
        //         \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
        //     ),
        //     false
        // );

        $parcelAttributes = array();

        foreach ($items as $item) {
            $this->addItem(
                $item->getSku(),
                $item->getName(),
                $item->getQtyOrdered(),
                $item->getRowWeight()
            );
        }

        return $this;
    }

    public function reset()
    {
        $this->setRetailerInvoice(null)
            ->setAuthorityToLeave(null)
            ->setDeliveryInstructions(null)
            ->setUserAttributes(null)
            ->setCourierType(null)
            ->setReceiverName(null)
            ->setReceiverContactNumber(null)
            ->setDeliveryAddress(null)
            ->setDeliverySuburb(null)
            ->setDeliveryPostcode(null)
            ->setDeliveryState(null)
            ->setParcelAttributes(null);
    }

    /**
     * Get the Retailer Invoice Referance
     *
     * @return string|null
     */
    public function getRetailerInvoice()
    {
        return $this->getData(self::RETAILER_INVOICE);
    }

    /**
     * Set the Retailer Invoice Referance
     *
     * @param string $orderDate
     * @return string
     */
    public function setRetailerInvoice($retailerInvoice)
    {
        return $this->setData(self::RETAILER_INVOICE, $retailerInvoice);
    }

    /**
     * Get the Authority To Leave
     *
     * @return bool|null
     */
    public function getAuthorityToLeave()
    {
        return $this->getData(self::AUTHORITY_TO_LEAVE);
    }

    /**
     * Set the Authority To Leave
     *
     * @param bool $authorityToLeave
     * @return bool
     */
    public function setAuthorityToLeave($authorityToLeave)
    {
        if ($authorityToLeave) {
            $authorityToLeave = "Yes";
        }
        else {
            $authorityToLeave = "No";
        }

        return $this->setData(self::AUTHORITY_TO_LEAVE, $authorityToLeave);
    }

    /**
     * Get the Delivery Instructions
     *
     * @return string|null
     */
    public function getDeliveryInstructions()
    {
        return $this->getData(self::DELIVERY_INSTRUCTIONS);
    }

    /**
     * Set the Delivery Instructions
     *
     * @param string $deliveryInstructions
     * @return string
     */
    public function setDeliveryInstructions($deliveryInstructions)
    {
        return $this->setData(self::DELIVERY_INSTRUCTIONS, $deliveryInstructions);
    }

    /**
     * Get the User Attributes
     *
     * @return array|null
     */
    public function getUserAttributes()
    {
        return $this->getData(self::USER_ATTRIBUTES);
    }

    /**
     * Set the User Attributes
     *
     * @param array $userAttributes
     * @return array
     */
    public function setUserAttributes($email, $firstname, $lastname)
    {
        $userAttributes = array(
            'email' => $email,
            'first_name' => $firstname,
            'last_name' => $lastname,
        );

        return $this->setData(self::USER_ATTRIBUTES, $userAttributes);
    }

    /**
     * Get the Courier Type
     *
     * @return array|null
     */
    public function getCourierType()
    {
        return $this->getData(self::COURIER_TYPE);
    }

    /**
     * Get the Courier Type
     *
     * @return array|null
     */
    public function setCourierType($courierType)
    {
        return $this->setData(self::COURIER_TYPE, $courierType);
    }

    /**
     * Get the Delivery Date
     *
     * @return string|null
     */
    public function getDeliveryDate()
    {
        return $this->getData(self::DELIVERY_DATE);
    }

    /**
     * Set the Delivery Date
     *
     * @param string $deliveryDate   Delivery Date
     * @return string
     */
    public function setDeliveryDate($deliveryDate)
    {
        return $this->setData(self::DELIVERY_DATE, $deliveryDate);
    }

    /**
     * Get the Delivery Window
     *
     * @return string|null
     */
    public function getDeliveryWindow()
    {
        return $this->getData(self::DELIVERY_WINDOW);
    }

    /**
     * Set the Delivery Window
     *
     * @param string $deliveryWindow   Delivery Window
     * @return string
     */
    public function setDeliveryWindow($deliveryWindow)
    {
        return $this->setData(self::DELIVERY_WINDOW, $deliveryWindow);
    }

    /**
     * Set the Shipping Method Values
     *
     * - Values may include the courier_type, delivery_date and delivery_window
     *
     * @param string|null $shippingMethod
     * @return array
     */
    public function setShippingMethod($shippingMethod = null)
    {
        // if the order is a premium delivery,
        // get the special delivery attributes
        if ($shippingMethod == 'premium') {
            $deliveryDate = $this->_getOrderDeliveryDate($this->order);
            $deliveryWindow = $this->_getOrderDeliveryWindow($this->order);
        }

        // set the courier details based on the shipping method
        if ($shippingMethod == 'standard') {
            return $this->setCourierType(self::SHIPPING_SERVICE_STANDARD);
        }
        elseif ($shippingMethod == 'express') {
            return $this->setCourierType(self::SHIPPING_SERVICE_EXPRESS);
        }
        elseif ($shippingMethod == 'premium' && isset($deliveryDate) && isset($deliveryWindow)) {
            return $this->setCourierType(self::SHIPPING_SERVICE_PREMIUM)
                ->setDeliveryDate($deliveryDate)
                ->setDeliveryWindow($deliveryWindow);
        }
        else {
            return $this->setData(self::COURIER_TYPE, self::SHIPPING_SERVICE_STANDARD);
        }
    }

    private function _getOrderDeliveryDate($order)
    {
        $shippingMethod = $order->getShippingMethod();

        // If the shipping method is a shippit method,
        // processing using the selected shipping options
        if (strpos($shippingMethod, $this->carrierCode) !== FALSE) {
            $shippingOptions = str_replace($this->carrierCode . '_', '', $shippingMethod);
            $shippingOptions = explode('_', $shippingOptions);
            $courierData = array();
            
            if (isset($shippingOptions[0])) {
                if ($shippingOptions[0] == 'Bonds') {
                    return $shippingOptions[1];
                }
                else {
                    return null;
                }
            }
            else {
                return null;
            }
        }
        else {
            return null;
        }
    }

    private function _getOrderDeliveryWindow($order)
    {
        $shippingMethod = $order->getShippingMethod();

        // If the shipping method is a shippit method,
        // processing using the selected shipping options
        if (strpos($shippingMethod, $this->carrierCode) !== FALSE) {
            $shippingOptions = str_replace($this->carrierCode . '_', '', $shippingMethod);
            $shippingOptions = explode('_', $shippingOptions);
            $courierData = array();
            
            if (isset($shippingOptions[0])) {
                if ($shippingOptions[0] == 'Bonds') {
                    return $shippingOptions[2];
                }
                else {
                    return null;
                }
            }
            else {
                return null;
            }
        }
        else {
            return null;
        }
    }

    /**
     * Get the Receiver Name
     *
     * @return string|null
     */
    public function getReceiverName()
    {
        return $this->getData(self::RECEIVER_NAME);
    }

    /**
     * Set the Reciever Name
     *
     * @param string $receiverName    Receiver Name
     * @return string
     */
    public function setReceiverName($receiverName)
    {
        return $this->setData(self::RECEIVER_NAME, $receiverName);
    }

    /**
     * Get the Receiver Contact Number
     *
     * @return string|null
     */
    public function getReceiverContactNumber()
    {
        return $this->getData(self::RECEIVER_CONTACT_NUMBER);
    }

    /**
     * Set the Reciever Contact Number
     *
     * @param string $receiverContactNumber    Receiver Contact Number
     * @return string
     */
    public function setReceiverContactNumber($receiverContactNumber)
    {
        return $this->setData(self::RECEIVER_CONTACT_NUMBER, $receiverContactNumber);
    }

    /**
     * Get the Delivery Address
     *
     * @return string|null
     */
    public function getDeliveryAddress()
    {
        return $this->getData(self::DELIVERY_ADDRESS);
    }

    /**
     * Set the Delivery Address
     *
     * @param string $deliveryAddress   Delivery Address
     * @return string
     */
    public function setDeliveryAddress($deliveryAddress)
    {
        return $this->setData(self::DELIVERY_ADDRESS, $deliveryAddress);
    }

    /**
     * Get the Delivery Suburb
     *
     * @return string|null
     */
    public function getDeliverySuburb()
    {
        return $this->getData(self::DELIVERY_SUBURB);
    }

    /**
     * Set the Delivery Suburb
     *
     * @param string $deliverySuburb   Delivery Suburb
     * @return string
     */
    public function setDeliverySuburb($deliverySuburb)
    {
        return $this->setData(self::DELIVERY_SUBURB, $deliverySuburb);
    }

    /**
     * Get the Delivery Postcode
     *
     * @return string|null
     */
    public function getDeliveryPostcode()
    {
        return $this->getData(self::DELIVERY_POSTCODE);
    }

    /**
     * Set the Delivery Postcode
     *
     * @param string $deliveryPostcode   Delivery Postcode
     * @return string
     */
    public function setDeliveryPostcode($deliveryPostcode)
    {
        return $this->setData(self::DELIVERY_POSTCODE, $deliveryPostcode);
    }

    /**
     * Get the Delivery State
     *
     * @return string|null
     */
    public function getDeliveryState()
    {
        return $this->getData(self::DELIVERY_STATE);
    }

    /**
     * Set the Delivery State
     *
     * @param string $deliveryState   Delivery State
     * @return string
     */
    public function setDeliveryState($deliveryState)
    {
        if (empty($deliveryState)) {
            $deliveryState = $this->helper->getStateFromPostcode($this->getDeliveryPostcode());
        }

        return $this->setData(self::DELIVERY_STATE, $deliveryState);
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

    /**
     * Add a parcel with attributes
     *
     */
    public function addItem($sku, $title, $qty, $weight = 0, $location = null)
    {
        $parcelAttributes = $this->getParcelAttributes();

        if (empty($parcelAttributes)) {
            $parcelAttributes = array();
        }

        $newParcel = array(
            'sku' => $sku,
            'title' => $title,
            'qty' => $qty,
            'weight' => $weight
        );

        $parcelAttributes[] = $newParcel;

        return $this->setParcelAttributes($parcelAttributes);
    }
}