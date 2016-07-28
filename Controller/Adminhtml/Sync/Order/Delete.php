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

namespace Shippit\Shipping\Controller\Adminhtml\Sync\Order;

class Delete extends \Magento\Backend\App\Action
{
    const ADMIN_ACTION = 'Shippit_Shipping::sync_order_delete';

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
        die('Shippit\Shipping\Controller\Adminhtml\Sync\Order\Delete');
        
        $id = $this->getRequest()->getParam('id');

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($id) {
            try {
                $model = $this->_objectManager
                    ->create('Shippit\Shipping\Api\Data\SyncOrderInterface')
                    ->load($id);
                    
                $model->delete();

                // display success message
                $this->messageManager->addSuccess(__('The Order Sync has been deleted.'));
            }
            catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
            }

            return $resultRedirect->setPath('*/*/');
        }

        // display error message
        $this->messageManager->addError(__('We can\'t find a Order Sync to delete.'));
        
        return $resultRedirect->setPath('*/*/');
    }
}