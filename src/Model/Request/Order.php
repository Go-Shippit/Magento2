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

use Shippit\Shipping\Api\Request\OrderInterface;
use Shippit\Shipping\Model\Config\Source\Shippit\Shipping\Methods as ShippingMethods;

class Order extends \Magento\Framework\Model\AbstractModel implements OrderInterface
{
    /**
     * @var \Shippit\Shipping\Helper\Data
     */
    protected $helper;

    /**
     * The carrier code used for Shippit Live Quotes
     *
     * @var string
     */
    protected $carrierCode;

    /**
     * The order object
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * THe Sync Order Object
     * @var \Shippit\Shipping\Api\Request\SyncOrderInterface
     */
    protected $syncOrder;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $localeResolver;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param \Shippit\Shipping\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Shippit\Shipping\Helper\Data $helper,
        \Shippit\Shipping\Api\Request\SyncOrderInterface $syncOrder,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->syncOrder = $syncOrder;
        $this->carrierCode = $helper::CARRIER_CODE;
        $this->localeResolver = $localeResolver;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function processSyncOrder(\Shippit\Shipping\Model\Sync\Order $syncOrder)
    {
        // get the order attached to the syncOrder object
        $order = $syncOrder->getOrder();
        // get the shipping method attached to the syncOrder object
        $shippingMethod = $syncOrder->getShippingMethod();
        // get the order items attached to the syncOrder queue
        $items = $syncOrder->getItemsCollection();

        // Build the order request
        $orderRequest = $this->setOrder($order)
            ->setItems($items)
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
            ->setDeliveryCompany($shippingAddress->getCompany())
            ->setDeliveryAddress(implode(' ', $shippingAddress->getStreet()))
            ->setDeliverySuburb($shippingAddress->getCity())
            ->setDeliveryPostcode($shippingAddress->getPostcode())
            ->setDeliveryState($shippingAddress->getRegionCode())
            ->setDeliveryCountry($shippingAddress->getCountryId())
            ->setSourcePlatform('magento2')
            ->setProductCurrency($this->order->getOrderCurrencyCode());

        // Set the receiver language code
        if (!empty($this->localeResolver->getLocale())) {
            $this->setReceiverLanguageCode(
                strstr($this->localeResolver->getLocale(), '_', true)
            );
        }

        $this->setOrderAfter($this->order);

        return $this;
    }

    public function setOrderAfter($order)
    {
        $deliveryState = $this->getDeliveryState();

        // If the delivery state is empty
        // Attempt to retrieve from the postcode lookup for AU Addresses
        if (empty($deliveryState) && $this->getDeliveryCountry() == 'AU') {
            $postcodeState = $this->helper->getStateFromPostcode($this->getDeliveryPostcode());

            if ($postcodeState) {
                $this->setDeliveryState($postcodeState);
            }
        }

        return $this;
    }

    /**
     * Add items from the order to the parcel details
     */
    public function setItems(\Shippit\Shipping\Model\ResourceModel\Sync\Order\Item\Collection $items)
    {
        if (count($items) == 0) {
            // If we don't have specific items in the request, build
            // the request dynamically from the order object
            $items = $this->syncOrder
                ->setOrder($this->order)
                ->setItems()
                ->getItems();

            $this->setParcelAttributes($items);
        }
        else {
            // Otherwise, use the data requested in the sync event
            foreach ($items as $item) {
                $this->addItem(
                    $item->getSku(),
                    $item->getTitle(),
                    $item->getQty(),
                    $item->getPrice(),
                    $item->getWeight(),
                    $item->getLength(),
                    $item->getWidth(),
                    $item->getDepth(),
                    $item->getLocation(),
                    $item->getTariffCode(),
                    $item->getOriginCountryCode(),
                    $item->getDangerousGoodsCode(),
                    $item->getDangerousGoodsText()
                );
            }
        }

        return $this;
    }

    public function reset()
    {
        // reset the request data
        $this->setData(self::RETAILER_INVOICE, null)
            ->setData(self::AUTHORITY_TO_LEAVE, null)
            ->setData(self::DELIVERY_INSTRUCTIONS, null)
            ->setData(self::USER_ATTRIBUTES, null)
            ->setData(self::COURIER_TYPE, null)
            ->setData(self::DELIVERY_DATE, null)
            ->setData(self::DELIVERY_WINDOW, null)
            ->setData(self::RECEIVER_NAME, null)
            ->setData(self::RECEIVER_CONTACT_NUMBER, null)
            ->setData(self::DELIVERY_COMPANY, null)
            ->setData(self::DELIVERY_ADDRESS, null)
            ->setData(self::DELIVERY_SUBURB, null)
            ->setData(self::DELIVERY_POSTCODE, null)
            ->setData(self::DELIVERY_STATE, null)
            ->setData(self::RECEIVER_LANGUAGE_CODE, null)
            ->setData(self::PARCEL_ATTRIBUTES, null);
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
     * @param string $retailerInvoice
     * @return self
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
     * @param bool|null $authorityToLeave
     * @return self
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
     * @return self
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
     * @param string|null $email
     * @param string|null $firstname
     * @param string|null $lastname
     * @return self
     */
    public function setUserAttributes($email, $firstname, $lastname)
    {
        $userAttributes = [
            'email' => $email,
            'first_name' => $firstname,
            'last_name' => $lastname,
        ];

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
     * @param string|null $courierType
     * @return self
     */
    public function setCourierType($courierType)
    {
        return $this->setData(self::COURIER_TYPE, $courierType);
    }

    /**
     * Get the Courier Allocation
     *
     * @return array|null
     */
    public function getCourierAllocation()
    {
        return $this->getData(self::COURIER_ALLOCATION);
    }

    /**
     * Get the Courier Allocation
     *
     * @param string $courierAllocation
     * @return self
     */
    public function setCourierAllocation($courierAllocation)
    {
        return $this->setData(self::COURIER_ALLOCATION, $courierAllocation);
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
     * @return self
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
     * @return self
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
     * @return self
     */
    public function setShippingMethod($shippingMethod = null)
    {
        // If the shipping method is a service level,
        // set the courier type attribute
        if (!empty($shippingMethod)
            && array_key_exists($shippingMethod, ShippingMethods::$serviceLevels)
        ) {
            $this->setCourierType($shippingMethod);

            // If the shipping method service level is priority,
            // process the delivery date and delivery window
            if ($shippingMethod == ShippingMethods::SERVICE_LEVEL_PRIORITY) {
                $deliveryDate = $this->getOrderDeliveryDate($this->order);
                $deliveryWindow = $this->getOrderDeliveryWindow($this->order);

                if (!empty($deliveryDate) && !empty($deliveryWindow)) {
                    $this->setDeliveryDate($deliveryDate);
                    $this->setDeliveryWindow($deliveryWindow);
                }
            }

            return $this;
        }
        // If shipping method is in the list of available
        // couriers then set a courier allocation
        elseif (!empty($shippingMethod)
            && array_key_exists($shippingMethod, ShippingMethods::$couriers)
        ) {
            return $this->setCourierAllocation($shippingMethod);
        }
        // Otherwise, if no matches are found, send
        // the order as a standard service level
        else {
            return $this->setCourierType(ShippingMethods::SERVICE_LEVEL_STANDARD);
        }
    }

    protected function getOrderDeliveryDate($order)
    {
        $shippingMethod = $order->getShippingMethod();

        // If the shipping method is a shippit method,
        // processing using the selected shipping options
        if (strpos($shippingMethod, $this->carrierCode) !== FALSE) {
            $shippingOptions = str_replace($this->carrierCode . '_', '', $shippingMethod);
            $shippingOptions = explode('_', $shippingOptions);
            $courierData = [];

            if (isset($shippingOptions[0])) {
                // Bonds Method Name matching has been added for
                // historical order shipping method support
                if ($shippingOptions[0] == 'Priority' || $shippingOptions[0] == 'Bonds') {
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

    protected function getOrderDeliveryWindow($order)
    {
        $shippingMethod = $order->getShippingMethod();

        // If the shipping method is a shippit method,
        // processing using the selected shipping options
        if (strpos($shippingMethod, $this->carrierCode) !== FALSE) {
            $shippingOptions = str_replace($this->carrierCode . '_', '', $shippingMethod);
            $shippingOptions = explode('_', $shippingOptions);
            $courierData = [];

            if (isset($shippingOptions[0])) {
                // Bonds Method Name matching has been added for
                // historical order shipping method support
                if ($shippingOptions[0] == 'Priority' || $shippingOptions[0] == 'Bonds') {
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
     * @return self
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
     * @return self
     */
    public function setReceiverContactNumber($receiverContactNumber)
    {
        return $this->setData(self::RECEIVER_CONTACT_NUMBER, $receiverContactNumber);
    }

    /**
     * Get the Delivery Company
     *
     * @return string|null
     */
    public function getDeliveryCompany()
    {
        return $this->getData(self::DELIVERY_COMPANY);
    }

    /**
     * Set the Delivery Company
     *
     * @param string $deliveryCompany   Delivery Company
     * @return self
     */
    public function setDeliveryCompany($deliveryCompany)
    {
        return $this->setData(self::DELIVERY_COMPANY, $deliveryCompany);
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
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
     */
    public function setDeliveryState($deliveryState)
    {
        return $this->setData(self::DELIVERY_STATE, $deliveryState);
    }

    /**
     * Get the Delivery Country
     *
     * @return string|null
     */
    public function getDeliveryCountry()
    {
        return $this->getData(self::DELIVERY_COUNTRY);
    }

    /**
     * Set the Delivery Country
     *
     * @param string $deliveryCountry   Delivery Country
     * @return self
     */
    public function setDeliveryCountry($deliveryCountry)
    {
        return $this->setData(self::DELIVERY_COUNTRY, $deliveryCountry);
    }

    /**
     * Get the Parcel Attributes
     *
     * @return array|null
     */
    public function getParcelAttributes()
    {
        return $this->getData(self::PARCEL_ATTRIBUTES);
    }

    /**
     * Set the Parcel Attributes
     *
     * @param array $parcelAttributes
     * @return self
     */
    public function setParcelAttributes($parcelAttributes)
    {
        return $this->setData(self::PARCEL_ATTRIBUTES, $parcelAttributes);
    }

    /**
     * Add a parcel with attributes
     *
     */
    public function addItem(
        $sku,
        $title,
        $qty,
        $price,
        $weight = 0,
        $length = null,
        $width = null,
        $depth = null,
        $location = null,
        $tariffcode = null,
        $originCountryCode = null,
        $dangerousGoodsCode = null,
        $dangerousGoodsText = null
    ) {
        $parcelAttributes = $this->getParcelAttributes();

        if (empty($parcelAttributes)) {
            $parcelAttributes = [];
        }

        $newParcel = [
            'sku' => $sku,
            'title' => $title,
            'qty' => (float) $qty,
            'price' => (float) $price,
            // if a 0 weight is provided, stub the weight to 0.2kg
            'weight' => (float) ($weight == 0 ? 0.2 : $weight),
            'location' => $location,
            'tariff_code' => $tariffcode,
            'origin_country_code' => $originCountryCode,
            'dangerous_goods_code' => $dangerousGoodsCode,
            'dangerous_goods_text' => $dangerousGoodsText,
        ];

        // for dimensions, ensure the item has values for all dimensions
        if (!empty($length) && !empty($width) && !empty($depth)) {
            $newParcel = array_merge(
                $newParcel,
                array(
                    'length' => (float) $length,
                    'width' => (float) $width,
                    'depth' => (float) $depth,
                )
            );
        }

        $parcelAttributes[] = $newParcel;

        return $this->setParcelAttributes($parcelAttributes);
    }

    /**
     * Set the Source Platform
     *
     * @param string $sourcePlatform
     * @return self
     */
    public function setSourcePlatform($sourcePlatform)
    {
        return $this->setData(self::SOURCE_PLATFORM, $sourcePlatform);
    }

    /**
     * Get the Source Platform
     *
     * @return string
     */
    public function getSourcePlatform()
    {
        return $this->getData(self::SOURCE_PLATFORM);
    }

    /**
     * Set the Product Currency
     *
     * @param string $productCurrency
     * @return self
     */
    public function setProductCurrency($productCurrency)
    {
        return $this->setData(self::PRODUCT_CURRENCY, $productCurrency);
    }

    /**
     * Get the Product Currency
     *
     * @return string
     */
    public function getProductCurrency()
    {
        return $this->getData(self::PRODUCT_CURRENCY);
    }

    /**
     * Set the Receiver Language Code
     *
     * @param string $receiverLanguageCode
     * @return self
     */
    public function setReceiverLanguageCode($receiverLanguageCode)
    {
        return $this->setData(self::RECEIVER_LANGUAGE_CODE, $receiverLanguageCode);
    }

    /**
     * Get the Receiver Language Code
     *
     * @return string
     */
    public function getReceiverLanguageCode()
    {
        return $this->getData(self::RECEIVER_LANGUAGE_CODE);
    }
}
