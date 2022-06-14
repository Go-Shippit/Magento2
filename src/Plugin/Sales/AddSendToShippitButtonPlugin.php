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

namespace Shippit\Shipping\Plugin\Sales;

class AddSendToShippitButtonPlugin
{
    protected $context;
    protected $url;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Backend\Model\UrlInterface $url
    ) {
        $this->context = $context;
        $this->url = $url;
    }

    public function afterGetButtonList(
        \Magento\Backend\Block\Widget\Context $subject,
        $buttonList
    ) {
        $request = $this->context->getRequest();

        if ($request->getFullActionName() == 'sales_order_view') {
            $buttonList->add(
                'shippit_send_order',
                [
                    'label' => __('Send to Shippit'),
                    'onclick' => 'setLocation(\'' . $this->getShippitOrderSyncUrl($request) . '\')',
                    'class' => 'ship'
                ],
                100
            );
        }

        return $buttonList;
    }

    public function getShippitOrderSyncUrl($request)
    {
        $orderId = $request->getParam('order_id');

        return $this->url->getUrl(
            'shippit/order/sync',
            [
                'order_id' => $orderId
            ]
        );
    }
}
