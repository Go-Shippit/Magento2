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

namespace Shippit\Shipping\Block\Adminhtml\Sync\Order;

use Magento\Backend\Block\Widget\Grid as WidgetGrid;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $orderConfig;

    /**
     * @var \Shippit\Shipping\Model\Resource\Subscription\Collection
     */
    protected $syncOrderCollection;

    /**
     * @var \Shippit\Shipping\Model\Config\Source\Shippit\Sync\Order\Status
     */
    protected $syncOrderStatus;

    /**
     * @var \Shippit\Shipping\Model\Config\Source\Shippit\Shipping\Methods
     */
    protected $shippitMethods;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Shippit\Shipping\Model\ResourceModel\Sync\Order\Collection $syncOrderCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Shippit\Shipping\Model\ResourceModel\Sync\Order\Collection $syncOrderCollection,
        \Shippit\Shipping\Model\Config\Source\Shippit\Sync\Order\Status $syncOrderStatus,
        \Shippit\Shipping\Model\Config\Source\Shippit\Shipping\Methods $shippitMethods,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);

        $this->setId('shippitSyncOrderGrid');
        $this->setDefaultSort('sync_order_id');
        $this->setDefaultDir('desc');

        $this->orderConfig = $orderConfig;
        $this->syncOrderCollection = $syncOrderCollection;
        $this->syncOrderStatus = $syncOrderStatus;
        $this->shippitMethods = $shippitMethods;

        $this->setEmptyText(__('No Shippit Order Sync Items Found'));
    }

    /**
     * Initialize the subscription collection
     *
     * @return WidgetGrid
     */
    protected function _prepareCollection()
    {
        $collection = $this->syncOrderCollection
            ->addFieldToSelect(
                [
                    'sync_order_id',
                    'shipping_method',
                    'tracking_number',
                    'synced_at',
                    'sync_status' => 'status'
                ]
            )
            ->join(
                [
                    'order' => $this->syncOrderCollection->getTable('sales_order')
                ],
                'main_table.order_id = order.entity_id',
                [
                    'increment_id'         => 'increment_id',
                    'grand_total'          => 'grand_total',
                    'order_state'          => 'state',
                    'order_status'         => 'status',
                    'created_at'           => 'created_at',
                ]
            )
            ->join(
                [
                    'order_address' => $this->syncOrderCollection->getTable('sales_order_address')
                ],
                'order.entity_id = order_address.parent_id AND order_address.address_type != \'billing\'',
                [
                    'firstname'    => 'firstname',
                    'lastname'     => 'lastname',
                ]
            )
            ->addFilterToMap(
                'sync_status',
                'main_table.status'
            )

            ->addFilterToMap(
                'order_state',
                'order.state'
            )
            ->addFilterToMap(
                'order_status',
                'order.status'
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
        $this->addColumn(
            'sync_order_id',
            [
                'header' => __('Sync #'),
                'index' => 'sync_order_id',
                'column_css_class' => 'no-display',
                'header_css_class' => 'no-display',
            ]
        );

        $this->addColumn(
            'increment_id',
            [
                'header' => __('Order #'),
                'index' => 'increment_id',
            ]
        );

        $this->addColumn(
            'purchased_on',
            [
                'header' => __('Purchased On'),
                'type' => 'datetime',
                'index' => 'created_at'
            ]
        );

        $this->addColumn(
            'shipping_method',
            [
                'header' => __('Shipping Method'),
                'index' => 'shipping_method',
                'type' => 'options',
                'options' => $this->shippitMethods->toArray(),
            ]
        );

        $this->addColumn(
            'order_state',
            [
                'header' => __('State'),
                'index' => 'order_state',
                'type' => 'options',
                'options' => $this->orderConfig->getStates(),
            ]
        );

        $this->addColumn(
            'order_status',
            [
                'header' => __('Status'),
                'index' => 'order_status',
                'type' => 'options',
                'options' => $this->orderConfig->getStatuses(),
            ]
        );

        $this->addColumn(
            'tracking_number',
            [
                'header' => __('Tracking Number'),
                'index' => 'tracking_number',
                'frame_callback' => [$this, 'decorateTrackingNumber'],
            ]
        );

        $this->addColumn(
            'synced_at',
            [
                'header' => __('Synced At'),
                'type' => 'datetime',
                'index' => 'synced_at'
            ]
        );

        $this->addColumn(
            'sync_status',
            [
                'header' => __('Sync Status'),
                'index' => 'sync_status',
                'type' => 'options',
                'options' => $this->syncOrderStatus->toArray(),
                'frame_callback' => [$this, 'decorateStatus'],
            ]
        );

        $this->addColumn(
            'actions',
            [
                'header' => __('Action'),
                'width' => '150px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Sync Now'),
                        'url' => ['base' => '*/*/sync'],
                        'field' => 'id'
                    ],
                    ['caption' => __('Schedule Sync'),
                        'url' => ['base' => '*/*/schedule'],
                        'field' => 'id'
                    ],
                    ['caption' => __('Delete'),
                        'url' => ['base' => '*/*/delete'],
                        'field' => 'id'
                    ],
                ],
                'filter' => false,
                'sortable' => false
            ]
        );

        return $this;
    }

    public function decorateTrackingNumber($value)
    {
        if (empty($value)) {
            return $value;
        }

        $cell = sprintf(
            '<a href="https://www.shippit.com/track/%s" title="Track Order" target="_blank">%s</a>',
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
            case \Shippit\Shipping\Model\Sync\Order::STATUS_PENDING_TEXT:
                $gridSeverity = 'minor';
                break;
            case \Shippit\Shipping\Model\Sync\Order::STATUS_SYNCED_TEXT:
                $gridSeverity = 'notice';
                break;
            case \Shippit\Shipping\Model\Sync\Order::STATUS_FAILED_TEXT:
                $gridSeverity = 'critical';
                break;
            default:
                $gridSeverity = 'critical';
        }

        return $gridSeverity;
    }
}
