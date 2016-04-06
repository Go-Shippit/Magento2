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

namespace Shippit\Shipping\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Shippit\Shipping\Model\Config\Source\Shippit\Sync\Order\Mode as SyncOrderMode;

use Magento\Sales\Model\Order;
use Shippit\Shipping\Model\Sync\Order as SyncOrder;

class addOrderToSyncQueueObserver implements ObserverInterface
{
    protected $syncOrderhelper;
    protected $carrier;
    protected $syncOrderFactory;
    protected $apiOrderFactory;
    protected $logger;

    protected $_hasAttemptedSync = false;
 
    public function __construct (
        \Shippit\Shipping\Helper\Sync\Order $syncOrderhelper,
        \Shippit\Shipping\Model\Carrier\Shippit $carrier,
        \Shippit\Shipping\Model\Sync\OrderFactory $syncOrderFactory,
        \Shippit\Shipping\Model\Api\OrderFactory $apiOrderFactory,
        \Shippit\Shipping\Logger\Logger $logger
    ) {
        $this->syncOrderhelper = $syncOrderhelper;
        $this->carrier = $carrier;
        $this->syncOrderFactory = $syncOrderFactory;
        $this->apiOrderFactory = $apiOrderFactory;
        $this->logger = $logger;
    }
 
    public function execute(Observer $observer)
    {
        // Ensure the module is active
        if (!$this->syncOrderhelper->isActive()) {
            return $this;
        }

        $order = $observer->getOrder();

        $shippingMethod = $order->getShippingMethod();
        $shippingCountry = $order->getShippingAddress()->getCountryId();

        // If send all orders + AU delivery, or shippit method is selected
        if (($this->syncOrderhelper->isSendAllOrdersActive() && $shippingCountry == 'AU')
            || strpos($shippingMethod, $this->carrier->getCarrierCode() !== FALSE)) {

            // create an order sync item
            $syncOrder = $this->syncOrderFactory->create();
            $syncOrder->addOrder($order);

            try {
                $syncOrder->save();

                // If the sync mode is realtime,
                // or the shipping method is a premium service,
                // attempt realtime sync now
                if ($this->syncOrderhelper->getSyncMode() == SyncOrderMode::REALTIME) {
                    $this->_syncOrder($syncOrder);
                }
            }
            catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->notice($e->getMessage());
            }
            catch (\RuntimeException $e) {
                $this->logger->notice($e->getMessage());
            }
            catch (\Exception $e) {
                $this->logger->notice($e->getMessage());
            }
        }

        return $this;
    }

    private function _syncOrder($syncOrder)
    {
        $order = $syncOrder->getOrder();

        if (!$this->_hasAttemptedSync
            // ensure the order is in the processing state
            && $order->getState() == Order::STATE_PROCESSING
            // ensure the sync order is in the pending state
            && $syncOrder->getStatus() == SyncOrder::STATUS_PENDING) {
            $this->_hasAttemptedSync = true;
            
            // attempt the sync
            $syncOrderResult = $this->apiOrderFactory->create()
                ->sync($syncOrder);

            return $syncOrderResult;
        }
        else {
            return false;
        }
    }
}