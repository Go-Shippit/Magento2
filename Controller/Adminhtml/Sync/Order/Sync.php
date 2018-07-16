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

use Exception;
use Magento\Framework\App\Area as AppArea;
use Shippit\Shipping\Model\Sync\Order as SyncOrder;

class Sync extends \Magento\Backend\App\Action
{
    const ADMIN_ACTION = 'Shippit_Shipping::sync_order_sync';

    protected $_syncOrderInterface;
    protected $_apiOrderFactory;
    protected $_appEmulation;

    public function __construct (
        \Magento\Backend\App\Action\Context $context,
        \Shippit\Shipping\Api\Data\SyncOrderInterface $syncOrderInterface,
        \Shippit\Shipping\Model\Api\OrderFactory $apiOrderFactory,
        \Magento\Store\Model\App\Emulation $emulation
    ) {
        $this->_syncOrderInterface = $syncOrderInterface;
        $this->_apiOrderFactory = $apiOrderFactory;
        $this->_appEmulation = $emulation;

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
     * {@inheritdoc}
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $syncOrderId = $this->getRequest()->getParam('id');

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (empty($syncOrderId)) {
            $this->messageManager->addError(__('The sync order could not be found.'));

            return $resultRedirect->setPath('*/*/');
        }

        $syncOrder = $this->_syncOrderInterface->load($syncOrderId);

        if (!$syncOrder) {
            $this->messageManager->addError(__('The sync order could not be found.'));

            return $resultRedirect->setPath('*/*/');
        }

        try {
            $syncOrder->setStatus(SyncOrder::STATUS_PENDING)
                ->setAttemptCount(0)
                ->setTrackingNumber(null)
                ->setSyncedAt(null)
                ->save();

            $storeId = $syncOrder->getOrder()->getStoreId();
            $environment = $this->_appEmulation->startEnvironmentEmulation(
                $storeId,
                AppArea::AREA_ADMINHTML
            );

            $request = $this->_apiOrderFactory
                ->create()
                ->sync($syncOrder, true);
        }
        catch (Exception $e) {
            // display error message
            $this->messageManager->addError($e->getMessage());
        }
        finally {
            $this->_appEmulation->stopEnvironmentEmulation();
        }

        return $resultRedirect->setPath('*/*/');
    }
}
