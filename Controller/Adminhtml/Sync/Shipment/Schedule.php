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

namespace Shippit\Shipping\Controller\Adminhtml\Sync\Shipment;

use \Shippit\Shipping\Model\Sync\Shipment as SyncShipment;

class Schedule extends \Magento\Backend\App\Action
{
    const ADMIN_ACTION = 'Shippit_Shipping::sync_shipment_schedule';

    /**
     * @var \Shippit\Shipping\Api\Data\SyncShipmentInterfaceFactory
     */
    protected $_syncShipmentInterfaceFactory;

    public function __construct(
        \Shippit\Shipping\Api\Data\SyncShipmentInterfaceFactory $syncShipmentInterfaceFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_syncShipmentInterfaceFactory = $syncShipmentInterfaceFactory;

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
     * Sync action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $syncId = $this->getRequest()->getParam('id');
        $this->_scheduleItems(array($syncId));
    }

    private function _scheduleItems($syncIds)
    {
        if (empty($syncIds)) {
            $this->messageManager->addError(__('You must select at least 1 shipment to sync'));
            $this->_redirect('*/*/index');

            return;
        }

        $syncShipments =  $this->_syncShipmentInterfaceFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter(
                'sync_shipment_id',
                array('in',$syncIds)
            );

        if ($syncShipments->getSize() == 0) {
            $this->messageManager->addError(__('No valid shipments were found'));

            $this->_redirect('*/*/index');

            return;
        }

        // reset the status of all items and attempts
        foreach ($syncShipments as $syncShipment) {
            $syncShipment->setStatus(SyncShipment::STATUS_PENDING)
                ->setAttemptCount(0)
                ->setShipmentIncrement(null)
                ->setTrackingNumber(null)
                ->setSyncedAt(null)
                ->save();
        }

        if (count($syncShipments) > 1) {
            $this->messageManager->addSuccess(__('The shipments have been successfully reset and will be processed again shortly'));
        }
        else {
            $this->messageManager->addSuccess(__('The shipments has been successfully reset and will be processed again shortly'));
        }
        $this->_redirect('*/*/index');

        return;
    }
}
