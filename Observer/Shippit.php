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

class Shippit_Shippit_Model_Observer_Shippit extends Mage_Core_Model_Abstract
{
    // protected $helper;

    // public function __construct() {
    //     $this->helper = Mage::helper('shippit/sync_order');

    //     return parent::__construct();
    // }

    // public function addOrder(Varien_Event_Observer $observer)
    // {
    //     // Ensure the module is active
    //     if (!$this->helper->isActive()) {
    //         return $this;
    //     }

    //     // get the event parameters
    //     $orderId = $observer->getEvent()->getEntityId();
    //     $items = $observer->getEvent()->getItems();

    //     if (empty($items)) {
    //         $items = array();
    //     }

    //     $apiKey = $observer->getEvent()->getApiKey();
    //     $syncMode = $observer->getEvent()->getSyncMode();
    //     $shippingMethod = $observer->getEvent()->getShippingMethod();

    //     $displayNotifications = $observer->getEvent()->getDisplayNotifications();

    //     if (empty($displayNotifications)) {
    //         $displayNotifications = false;
    //     }

    //     // save the request to sync the order, and sync immediately if realtime
    //     Mage::getModel('shippit/shippit')->addOrder($orderId, $items, $shippingMethod, $apiKey, $syncMode, $displayNotifications);

    //     return $this;
    // }
}