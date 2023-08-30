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
 * @category  Shippit
 * @author    Matthew Muscat <matthew@mamis.com.au>
 * @copyright Copyright (c) by Shippit Pty Ltd (http://www.shippit.com)
 * @license   http://www.shippit.com/terms
 */

namespace Shippit\Shipping\Block\Adminhtml\Sync\Shipment;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Shippit\Shipping\Model\Resource\Subscription\Collection
     */
    protected $syncShipmentCollection;

    /**
     * @var  \Shippit\Shipping\Model\Config\Source\Shippit\Sync\Shipment\Status
     */
    protected $syncShipmentStatus;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Shippit\Shipping\Model\ResourceModel\Sync\Order\Collection $syncOrderCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Shippit\Shipping\Model\ResourceModel\Sync\Shipment\Collection $syncShipmentCollection,
        \Shippit\Shipping\Model\Config\Source\Shippit\Sync\Shipment\Status $syncShipmentStatus,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);

        $this->setId('shippitSyncShipmentGrid');
        $this->setDefaultSort('sync_shipment_id');
        $this->setDefaultDir('desc');
        $this->syncShipmentCollection = $syncShipmentCollection;
        $this->syncShipmentStatus = $syncShipmentStatus;
        $this->setEmptyText(__('No Shippit Shipment Sync Items Found'));
    }

    /**
     * Initialize the subscription collection
     *
     * @return WidgetGrid
     */
    protected function _prepareCollection()
    {

        $collection = $this->syncShipmentCollection
            ->addFieldToSelect(
                [
                    'sync_shipment_id',
                    'store_id',
                    'order_increment',
                    'shipment_increment',
                    'status',
                    'courier_allocation',
                    'track_number',
                    'attempt_count',
                    'created_at',
                    'synced_at',

                ]
            );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('sync_shipment_id', array(
            'header' => __('ID'),
            'index'  => 'sync_shipment_id',
            'column_css_class' => '',
            'header_css_class' => '',
        ));

        $this->addColumn('shipment_increment', array(
            'header' => __('Shipment #'),
            'index'  => 'shipment_increment',
        ));

        $this->addColumn('order_increment', array(
            'header' => __('Order #'),
            'index'  => 'order_increment',
        ));

        $this->addColumn('track_number', array(
            'header' => __('Shippit Reference'),
            'index'  => 'track_number',
            'frame_callback' => array($this, 'decorateTrackNumber'),
        ));

        $this->addColumn('courier_allocation', array(
            'header' => __('Courier'),
            'index'  => 'courier_allocation',
            'frame_callback' => array($this, 'decorateCourierAllocation'),
        ));

        $this->addColumn('status', array(
            'header'  => __('Status'),
            'index'   => 'status',
            'type'    => 'options',
            'options' => $this->syncShipmentStatus->toArray(),
        ));

        $this->addColumn('created_at', array(
            'header' => __('Created At'),
            'type'   => 'datetime',
            'index'  => 'created_at',
        ));

        $this->addColumn('synced_at', array(
            'header' => __('Synced At'),
            'type'   => 'datetime',
            'index'  => 'synced_at',
        ));

        $this->addColumn('actions', array(
            'header' => __('Action'),
            'width' => '150px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Sync Now'),
                    'url' => array('base' => '*/*/sync'),
                    'field' => 'id',
                ),
                array(
                    'caption' => __('Schedule Sync'),
                    'url' => array('base' => '*/*/schedule'),
                    'field' => 'id',
                ),
                array(
                    'caption' => __('Delete'),
                    'url' => array('base' => '*/*/delete'),
                    'field' => 'id',
                ),
                'filter' => false,
                'sortable' => false,
            ),
        ));

        return $this;
    }

    public function decorateTrackNumber($value)
    {
        if (empty($value)) {
            return $value;
        }

        $cell = sprintf(
            '<a href="https://www.shippit.com/track/%s" title="Track Shipment" target="_blank">%s</a>',
            $value,
            $value
        );

        return $cell;
    }

    public function decorateStatus($value)
    {
        $cell = sprintf(
            '<span class="grid-severity-%s"><span>%s</span></span>',
            $this->getStatusSeverity($value),
            $value
        );

        return $cell;
    }

    public function getStatusSeverity($value)
    {
        switch ($value) {
            case \Shippit\Shipping\Model\Sync\Shipment::STATUS_PENDING_TEXT:
                $gridSeverity = 'minor';
                break;
            case \Shippit\Shipping\Model\Sync\Shipment::STATUS_SYNCED_TEXT:
                $gridSeverity = 'notice';
                break;
            case \Shippit\Shipping\Model\Sync\Shipment::STATUS_FAILED_TEXT:
                $gridSeverity = 'critical';
                break;
            default:
                $gridSeverity = 'critical';
        }

        return $gridSeverity;
    }

    public function decorateCourierAllocation($value)
    {
        return ucfirst($value);
    }
}
