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

namespace Shippit\Shipping\Controller\Adminhtml\Sync\Order;

class Sync extends \Magento\Backend\App\Action
{
    const ADMIN_ACTION = 'Shippit_Shipping::sync_order_sync';

    protected $_logger;
    protected $_appEmulation;

    public function __construct (
        \Magento\Backend\App\Action\Context $context,
        \Magento\Store\Model\App\Emulation $emulation,
        \Shippit\Shipping\Logger\Logger $logger
    ) {
        $this->_appEmulation = $emulation;
        $this->_logger = $logger;

        parent::__construct($context);
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
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($id) {
            try {
                $syncOrder = $this->_objectManager
                    ->create('Shippit\Shipping\Api\Data\SyncOrderInterface')
                    ->load($id);

                $syncOrder->setStatus(\Shippit\Shipping\Model\Sync\Order::STATUS_PENDING)
                    ->setAttemptCount(0)
                    ->setTrackingNumber(null)
                    ->setSyncedAt(null)
                    ->save();

                $storeId = $syncOrder->getOrder()->getStoreId();
                $environment = $this->_appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_ADMINHTML, true);

                $request = $this->_objectManager
                    ->create('Shippit\Shipping\Model\Api\Order')
                    ->sync($syncOrder, true);
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
            } finally {
                $this->_appEmulation->stopEnvironmentEmulation();
            }

            return $resultRedirect->setPath('*/*/');
        }

        // display error message
        $this->messageManager->addError(__('We can\'t find a Order Sync to schedule.'));

        return $resultRedirect->setPath('*/*/');
    }
}
