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

use Shippit\Shipping\Api\Request\ShipmentInterface;
use Magento\Framework\Exception\LocalizedException;

// Read the Shippit webhook request and provides
// a summary of the available item actions in Magento

class Shipment extends \Magento\Framework\Model\AbstractModel implements ShipmentInterface
{
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $orderInterface;

    /**
     * @var \Magento\Sales\Api\Data\OrderItemInterface
     */
    protected $orderItemInterface;

    /**
     * @var \Shippit\Shipping\Helper\Sync\Order\Items
     */
    protected $helper;

    /**
     * @var array
     */
    protected $items = null;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Api\Data\OrderInterface $orderInterface
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItemInterface
     * @param \Shippit\Shipping\Helper\Sync\Order\Items $helper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Sales\Api\Data\OrderItemInterface $orderItemInterface,
        \Shippit\Shipping\Helper\Sync\Order\Items $helper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->orderInterface = $orderInterface;
        $this->orderItemInterface = $orderItemInterface;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function getOrderId()
    {
        return $this->getOrder()->getEntityId();
    }

    public function setOrderByIncrementId($incrementId)
    {
        $order = $this->orderInterface
            ->load($incrementId, 'increment_id');

        return $this->setOrder($order);
    }

    public function getOrder()
    {
        return $this->getData(self::ORDER);
    }

    public function setOrder($order)
    {
        if (!$order->getId()) {
            throw new LocalizedException(
                __(self::ERROR_ORDER_MISSING)
            );
        }

        if (!$order->canShip()) {
            throw new LocalizedException(
                __(self::ERROR_ORDER_STATUS)
            );
        }

        return $this->setData(self::ORDER, $order);
    }

    /**
     * Process items in the shipment request,
     * - ensures only items contained in the order are present
     * - ensures only qtys available for shipping are used in the shipment
     *
     * @param array $items
     * @return self
     */
    public function processItems($items = [])
    {
        // store items on the internal model property
        $this->items = $items;

        $itemsCollection = $this->orderItemInterface
            ->getCollection()
            ->addFieldToFilter('order_id', $this->getOrderId());

        // for the specific items that have been passed, ensure they are valid
        // items for the item
        if (!empty($items)) {
            $itemsSkus = $this->helper->getSkus($items);

            if (!empty($itemsSkus)) {
                $itemsCollection->addFieldToFilter('sku', ['in' => $itemsSkus]);
            }
        }

        // For all valid items, process the quantity to be marked as shipped
        foreach ($itemsCollection as $item) {
            $requestedQty = $this->helper->getItemData($items, 'sku', $item->getSku(), 'qty');

            /**
             * Magento marks a shipment only for the parent item in the order
             * get the parent item to determine the correct qty to ship
             */
            $rootItem = $this->_getRootItem($item);

            $itemQty = $this->helper->getQtyToShip($rootItem, $requestedQty);

            if ($itemQty > 0) {
                $this->addItem($item->getId(), $itemQty);
            }
        }

        return $this;
    }

    protected function _getRootItem($item)
    {
        if ($item->hasParentItem()) {
            return $item->getParentItem();
        }
        else {
            return $item;
        }
    }

    /**
     * Get the Items in the request
     *
     * @return array|null
     */
    public function getItems()
    {
        // if no items have been added, assume all items are to be marked as shipped
        if (empty($this->getData(self::ITEMS))) {
            return [];
        }
        // otherwise, only mark the items and qtys specified as shipped
        else {
            return $this->getData(self::ITEMS);
        }
    }

    /**
     * Set the items in the request
     *
     * @param array $items The items to be included in the request
     * @return self
     */
    public function setItems($items)
    {
        return $this->setData(self::ITEMS, $items);
    }

    /**
     * Add the item in the request
     *
     * @param string $itemId The Item Id to be Shipped
     * @param float $qty     The Item Qty to be Shipped
     * @return self
     */
    public function addItem($itemId, $qty)
    {
        $items = $this->getItems();

        $items[$itemId] = $qty;

        return $this->setItems($items);
    }
}
