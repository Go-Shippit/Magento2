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

namespace Shippit\Shipping\Controller\Adminhtml\Order;

class MassSync extends \Magento\Backend\App\Action
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
        $selected = $this->getRequest()->getParam('selected', null);

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (empty($selected)) {
            $this->messageManager->addError(__('Please select an order to schedule.'));

            return $resultRedirect->setPath('sales/order/index/');
        }

        $orders = $this->_objectManager
            ->get('\Magento\Sales\Api\Data\OrderSearchResultInterface');

        $orders->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('shipping_method')
            ->addAttributeToFilter('entity_id', ['in' => $selected]);

        try {
            foreach ($orders as $order) {
                $this->_eventManager->dispatch(
                    'shippit_add_order',
                    [
                        'order' => $order->getId(),
                        'shipping_method' => $order->getShippingMethod()
                    ]
                );
            }

            // display error message
            $this->messageManager->addSuccess(__('The selected orders have been scheduled to sync with Shippit'));
        } catch (\Exception $e) {
            // display error message
            $this->messageManager->addError($e->getMessage());
        }

        return $resultRedirect->setPath('sales/order/index/');
    }
}
