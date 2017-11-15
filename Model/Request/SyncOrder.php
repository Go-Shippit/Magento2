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

use \Magento\Framework\Exception\LocalizedException;

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
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_itemsHelper = $itemsHelper;
        $this->_orderItemInterface = $orderItemInterface;

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
        // Standard, express Standard, express, priority and click_and_collect options are available
        // Priority services requires the use of live quoting to determine
        // booking availability
        $validShippingMethods = [
            'standard',
            'express',
            'priority',
            'click_and_collect',
        ];

        // if the shipping method passed is not a standard shippit service class, attempt to get a service class based on the configured mapping
        if (!in_array($shippingMethod, $validShippingMethods)) {
            $shippingMethod = $this->_helper->getShippitShippingMethod($shippingMethod);
        }

        if (in_array($shippingMethod, $validShippingMethods)) {
            return $this->setData(self::SHIPPING_METHOD, $shippingMethod);
        }
        else {
            return $this->setData(self::SHIPPING_METHOD, 'standard');
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
                $this->getItemLocation($item)
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

    protected function getItemSku($item)
    {
        return $item->getSku();
    }

    protected function getItemName($item)
    {
        return $item->getName();
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
            // if we are sending the bundle together
            if ($rootItem->getId() == $item->getId()) {
                return $rootItem->getBasePriceInclTax();
            }
            // if we are sending individually
            else {
                return $item->getBasePriceInclTax();
            }
        }
        else {
            return $rootItem->getBasePriceInclTax();
        }
    }

    protected function getItemWeight($item)
    {
        return $item->getWeight();
    }

    protected function getItemLocation($item)
    {
        return $this->_itemsHelper->getLocation($item);
    }

    /**
     * Add a parcel with attributes
     *
     */
    public function addItem($sku, $title, $qty, $price, $weight = 0, $location = null)
    {
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
            'location' => $location
        ];

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
