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

use Magento\Framework\Exception\LocalizedException;
use Shippit\Shipping\Model\Config\Source\Shippit\Shipping\Methods as ShippingMethods;

class SyncOrder extends \Magento\Framework\Model\AbstractModel implements \Shippit\Shipping\Api\Request\SyncOrderInterface
{
    const ERROR_NO_ITEMS_AVAILABLE_FOR_SHIPPING = 'No items could be added to the sync order request, please ensure the items are available for shipping';

    /**
     * @var \Shippit\Shipping\Helper\Sync\Order
     */
    protected $_helper;

    /**
     * @var \Shippit\Shipping\Helper\Sync\Order\Items
     */
    protected $_itemsHelper;

    /**
     * @var \Magento\Sales\Api\Data\OrderItemInterface
     */
    protected $_orderItemInterface;

    protected $_directoryHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Shippit\Shipping\Helper\Sync\Order $helper
     * @param \Shippit\Shipping\Helper\Sync\Order\Items $itemsHelper
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItemInterface,
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Shippit\Shipping\Helper\Sync\Order $helper,
        \Shippit\Shipping\Helper\Sync\Order\Items $itemsHelper,
        \Magento\Sales\Api\Data\OrderItemInterface $orderItemInterface,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_itemsHelper = $itemsHelper;
        $this->_orderItemInterface = $orderItemInterface;
        $this->_directoryHelper = $directoryHelper;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Get the API Key
     *
     * @return string|null
     */
    public function getApiKey()
    {
        return $this->getData(self::API_KEY);
    }

    /**
     * Set the API Key
     *
     * @param string $apiKey
     * @return string|null
     */
    public function setApiKey($apiKey)
    {
        return $this->setData(self::API_KEY, $apiKey);
    }

    /**
     * Get the Order Id
     *
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Set the Order Id
     *
     * @param string $orderId
     * @return string|null
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get the Order Object
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     */
    public function getOrder()
    {
        return $this->getData(self::ORDER);
    }

    /**
     * Set the Order Object
     *
     * @param string $orderId
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     */
    public function setOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        if ($order instanceof \Magento\Sales\Api\Data\OrderInterface) {
            $this->setData(self::ORDER, $order);
            $this->setOrderId($order->getId());
        }
        else {
            $this->setData(self::ORDER, $order);
            $this->setOrderId($order);
        }

        return $this;
    }

    /**
     * Get the Shipping Method
     *
     * @return string|null
     */
    public function getShippingMethod()
    {
        return $this->getData(self::SHIPPING_METHOD);
    }

    /**
     * Set the Shipping Method
     *
     * @param string $shippingMethod
     * @return string|null
     */
    public function setShippingMethod($shippingMethod)
    {
        // if the shipping method passed is not a recognised
        // service level or courier, attempt to retrive the
        // shipping method based on the shipping method mapping
        if (!empty($shippingMethod)
            && (
                !array_key_exists($shippingMethod, ShippingMethods::$serviceLevels)
                && !array_key_exists($shippingMethod, ShippingMethods::$couriers)
            )
        ) {
            $shippingMethod = $this->_helper->getShippitShippingMethod($shippingMethod);
        }

        // Process the shipping method using the Shippit
        // Service Level / Carrier List
        if (!empty($shippingMethod)
            && (
                array_key_exists($shippingMethod, ShippingMethods::$serviceLevels)
                || array_key_exists($shippingMethod, ShippingMethods::$couriers)
            )
        ) {
            return $this->setData(self::SHIPPING_METHOD, $shippingMethod);
        }
        else {
            return $this->setData(self::SHIPPING_METHOD, ShippingMethods::SERVICE_LEVEL_STANDARD);
        }
    }

    /**
     * Retrieve the sync order items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->getData(self::ITEMS);
    }

    /**
     * Set the sync order items
     *
     * @return \Shippit\Shipping\Api\Request\SyncOrderInterface
     */
    public function setItems($items = [])
    {
        $itemsCollection = $this->_orderItemInterface
            ->getCollection()
            ->addFieldToFilter('order_id', $this->getOrderId());

        // if specific items have been passed,
        // ensure that these are the only items in the request
        if (!empty($items)) {
            $itemsSkus = $this->_itemsHelper->getSkus($items);

            if (!empty($itemsSkus)) {
                $itemsCollection = $itemsCollection->addFieldToFilter(
                    'sku',
                    [
                        'in' => $itemsSkus
                    ]
                );
            }
        }

        $itemsAdded = 0;

        foreach ($itemsCollection as $item) {
            // Skip the item if...
            // - it is a dummy item not required for shipment
            // - it is a virtual item
            if ($item->isDummy(true) || $item->getIsVirtual()) {
                continue;
            }

            $itemQty = $this->getItemQty($items, $item);

            // If the item qty is 0, skip this item from being sent to Shippit
            if ($itemQty <= 0) {
                continue;
            }

            $this->addItem(
                $this->getItemSku($item),
                $this->getItemName($item),
                $itemQty,
                $this->getItemPrice($item),
                $this->getItemWeight($item),
                $this->getItemLength($item),
                $this->getItemWidth($item),
                $this->getItemDepth($item),
                $this->getItemLocation($item),
                $this->getItemTariffCode($item),
                $this->getOriginCountryCode($item)
            );

            $itemsAdded++;
        }

        if ($itemsAdded == 0) {
            throw new LocalizedException(
                __(self::ERROR_NO_ITEMS_AVAILABLE_FOR_SHIPPING)
            );
        }

        return $this;
    }

    protected function _getRootItem($item)
    {
        if ($item->getParentItem()) {
            return $item->getParentItem();
        }
        else {
            return $item;
        }
    }

    /**
     * Returns the first child item of the item passed
     * - If the item is a bundle and is being shipped together
     *   we return the bundle item, as it's the "shipped" product
     *
     * @param  Mage_Sales_Model_Order_Item $item
     * @return Mage_Sales_Model_Order_Item
     */
    protected function _getChildItem($item)
    {
        if ($item->getHasChildren()) {
            $rootItem = $this->_getRootItem($item);

            // Get the first child item
            // - If the root item is a bundle, use the item
            //   Otherwise, use the root item
            if ($rootItem->getProductType() == 'bundle') {
                // if we are sending the bundle together
                if ($rootItem->getId() == $item->getId()) {
                    return $rootItem;
                }
                else {
                    $items = $item->getChildrenItems();

                    return reset($items);
                }
            }
            else {
                $items = $item->getChildrenItems();

                return reset($items);
            }
        }
        else {
            return $item;
        }
    }

    protected function getItemSku($item)
    {
        return $item->getSku();
    }

    protected function getItemName($item)
    {
        $childItem = $this->_getChildItem($item);

        return $childItem->getName();
    }

    protected function getItemQty($items, $item)
    {
        $requestedQty = $this->getRequestedQuantity($items, 'sku', $item->getSku(), 'qty');

        return $this->_itemsHelper->getQtyToShip($item, $requestedQty);
    }

    protected function getRequestedQuantity($items, $itemKey, $itemSku, $itemDataKey)
    {
        return $this->_itemsHelper->getItemData($items, $itemKey, $itemSku, $itemDataKey);
    }

    protected function getItemPrice($item)
    {
        $rootItem = $this->_getRootItem($item);

        // Get the item price
        // - If the root item is a bundle, use the item price
        //   Otherwise, use the root item price
        if ($rootItem->getProductType() == 'bundle') {
            return $this->getBundleItemPrice($item);
        }
        else {
            return $this->getBasicItemPrice($item);
        }
    }

    protected function getBundleItemPrice($item)
    {
        $rootItem = $this->_getRootItem($item);

        // if we are sending the bundle together
        if ($rootItem->getId() == $item->getId()) {
            $childItems = $rootItem->getChildrenItems();
            $itemPrice = 0;

            foreach ($childItems as $childItem) {
                // Get the number of items in the bundle per bundle package purchased
                $childItemQty = ($childItem->getQtyOrdered() / $rootItem->getQtyOrdered());
                $rowTotalAfterDiscounts = $childItem->getRowTotalInclTax() - $childItem->getDiscountAmount();
                $rowUnitPrice = $rowTotalAfterDiscounts / $childItem->getQtyOrdered();
                $bundleItemUnitPrice = $rowUnitPrice * $childItemQty;

                $itemPrice += $bundleItemUnitPrice;
            }

            return round($itemPrice, 2);
        }
        // if we are sending the bundle individually
        else {
            return $this->getBasicItemPrice($item);
        }
    }

    protected function getBasicItemPrice($item)
    {
        $rowTotalAfterDiscounts = $item->getRowTotalInclTax() - $item->getDiscountAmount();
        $itemPrice = $rowTotalAfterDiscounts / $item->getQtyOrdered();

        return round($itemPrice, 2);
    }

    protected function getItemWeight($item)
    {
        return $item->getWeight();
    }

    protected function getItemLength($item)
    {
        if (!$this->_itemsHelper->isProductDimensionActive()) {
            return;
        }

        $childItem = $this->_getChildItem($item);

        return $this->_itemsHelper->getLength($childItem);
    }

    protected function getItemWidth($item)
    {
        if (!$this->_itemsHelper->isProductDimensionActive()) {
            return;
        }

        $childItem = $this->_getChildItem($item);

        return $this->_itemsHelper->getWidth($childItem);
    }

    protected function getItemDepth($item)
    {
        if (!$this->_itemsHelper->isProductDimensionActive()) {
            return;
        }

        $childItem = $this->_getChildItem($item);

        return $this->_itemsHelper->getDepth($childItem);
    }

    protected function getItemLocation($item)
    {
        if (!$this->_itemsHelper->isProductLocationActive()) {
            return;
        }

        $childItem = $this->_getChildItem($item);

        return $this->_itemsHelper->getLocation($childItem);
    }

    protected function getItemTariffCode($item)
    {
        if (!$this->_itemsHelper->isProductTariffCodeActive()) {
            return;
        }

        $rootItem = $this->_getRootItem($item);
        $childItem = $this->_getChildItem($item);

        // Attempt to retrieve the tariff code from the child item
        $tariffCode = $this->_itemsHelper->getTariffCode($childItem);

        // If product has a parent product and the child item
        // does not have tariff code value set, attempt to
        // use the root product tariff code value
        if (
            $rootItem != $childItem
            && empty($tariffCode)
        ) {
            $tariffCode = $this->_itemsHelper->getTariffCode($rootItem);
        }

        return $tariffCode;
    }

    protected function getOriginCountryCode($item)
    {
        if (!$this->_itemsHelper->isProductOriginCountryCodeActive()) {
            return;
        }

        $rootItem = $this->_getRootItem($item);
        $childItem = $this->_getChildItem($item);

        // Attempt to retrieve the origin country from the child item
        $originCountryCode = $this->_itemsHelper->getOriginCountryCode($childItem);

        // If product has a parent product and the child item
        // does not have origin country code value set,
        // attempt to use the root product origin
        // country code value
        if (
            $rootItem != $childItem
            && empty($originCountryCode)
        ) {
            $originCountryCode = $this->_itemsHelper->getOriginCountryCode($rootItem);
        }

        // If the value is 2 characters, assume this is a valid ISO2 code standard
        // Otherwise, attempt to lookup the country by name / ISO3 code and
        // convert this value into ISO2
        if (strlen($originCountryCode) > 2) {
            $countryCollection = $this->_directoryHelper->getCountryCollection();
            $countryData = [];

            foreach ($countryCollection as $country) {
                $countryData[] = [
                    'name' => $country->getName(),
                    'iso2_code' => $country->getData('iso2_code'),
                    'iso3_code' => $country->getData('iso3_code'),
                ];
            }

            // Attempt to lookup using the name or iso3 code
            $countriesFound = array_filter($countryData, function($country) use ($originCountryCode) {
                return (
                    $country['iso3_code'] == $originCountryCode
                    || $country['name'] == $originCountryCode
                );
            });

            // If we have at least 1 country match, set this as the origin country code
            if (!empty($countriesFound)) {
                $originCountryCode = reset($countriesFound)['iso2_code'];
            }
        }

        return $originCountryCode;
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
        $tariffCode = null,
        $originCountryCode = null
    ) {
        $items = $this->getItems();

        if (empty($items)) {
            $items = [];
        }

        $newItem = [
            'sku' => $sku,
            'title' => $title,
            'qty' => (float) $qty,
            'price' => (float) $price,
            'weight' => (float) $weight,
            'location' => $location,
            'tariff_code' => $tariffCode,
            'origin_country_code' => $originCountryCode,
        ];

        // for dimensions, ensure the item has values for all dimensions
        if (!empty($length) && !empty($width) && !empty($depth)) {
            $newItem = array_merge(
                $newItem,
                array(
                    'length' => (float) $length,
                    'width' => (float) $width,
                    'depth' => (float) $depth
                )
            );
        }

        $items[] = $newItem;

        return $this->setData(self::ITEMS, $items);
    }

    /**
     * Reset this instance of data to default values
     *
     * @return \Shippit\Shipping\Model\Request\SyncOrder
     */
    public function reset()
    {
        return $this->setData(self::API_KEY, null)
            ->setData(self::ORDER_ID, null)
            ->setData(self::ORDER, null)
            ->setData(self::SHIPPING_METHOD, null)
            ->setData(self::ITEMS, null);
    }
}
