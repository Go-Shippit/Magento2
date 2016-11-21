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
 * @copyright  Copyright (c) 2016 by Shippit Pty Ltd (http://www.shippit.com)
 * @author     Matthew Muscat <matthew@mamis.com.au>
 * @license    http://www.shippit.com/terms
 */

namespace Shippit\Shipping\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Shippit implements ObserverInterface
{
    protected $_helper;
    protected $_shippit;

    public function __construct (
        \Shippit\Shipping\Helper\Sync\Order $helper,
        \Shippit\Shipping\Model\Shippit $shippit
    ) {
        $this->_helper = $helper;
        $this->_shippit = $shippit;
    }

    public function execute(Observer $observer)
    {
        // Ensure the module is active
        if (!$this->_helper->isActive()) {
            return $this;
        }

        // get the event parameters
        $order = $observer->getEvent()->getOrder();
        $items = $observer->getEvent()->getItems();

        if (empty($items)) {
            $items = [];
        }

        $apiKey = $observer->getEvent()->getApiKey();
        $syncMode = $observer->getEvent()->getSyncMode();
        $shippingMethod = $observer->getEvent()->getShippingMethod();

        $displayNotifications = $observer->getEvent()->getDisplayNotifications();

        if (empty($displayNotifications)) {
            $displayNotifications = false;
        }

        // save the request to sync the order, and sync immediately if realtime
        $this->_shippit
            ->addOrder(
                $order,
                $items,
                $shippingMethod,
                $apiKey,
                $syncMode,
                $displayNotifications
        );

        return $this;
    }
}
