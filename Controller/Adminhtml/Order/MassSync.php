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

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class MassSync extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    const ADMIN_ACTION = 'Shippit_Shipping::order_sync';

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
    }

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
     * Mass Sync Action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function massAction(AbstractCollection $collection)
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            foreach ($collection->getItems() as $order) {
                if (!$order->getId()) {
                    continue;
                }

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
