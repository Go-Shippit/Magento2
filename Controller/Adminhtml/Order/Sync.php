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

namespace Shippit\Shipping\Controller\Adminhtml\Order;

class Sync extends \Magento\Backend\App\Action
{
    const ADMIN_ACTION = 'Shippit_Shipping::order_sync';

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_ACTION);
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id', null);

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (empty($orderId)) {
            $this->messageManager->addError(__('We can\'t find the Order to schedule.'));

            return $resultRedirect->setPath('sales/order/view/', ['order_id' => $orderId]);
        }

        $order = $this->_objectManager
            ->get('\Magento\Sales\Api\Data\OrderInterface')
            ->load($orderId);

        if (!$order) {
            $this->messageManager->addError(__('We can\'t find the Order to schedule.'));

            return $resultRedirect->setPath('sales/order/view/', ['order_id' => $orderId]);
        }
     
        try {
            $this->_eventManager->dispatch(
                'shippit_add_order',
                [
                    'order' => $order->getId(),
                    'sync_mode' => 'realtime',
                    'shipping_method' => $order->getShippingMethod(),
                    'display_notifications' => true
                ]
            );
        } catch (\Exception $e) {
            // display error message
            $this->messageManager->addError($e->getMessage());
        }
        
        return $resultRedirect->setPath('sales/order/view/', ['order_id' => $orderId]);
    }
}
