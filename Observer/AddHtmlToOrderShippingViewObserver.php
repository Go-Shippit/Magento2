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

namespace Shippit\Shipping\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddHtmlToOrderShippingViewObserver implements ObserverInterface
{
    /**
     * @var \Shippit\Shipping\Helper\Checkout
     */
    protected $helper;

    /**
     * @var \Magento\Framework\View\Element\TemplateFactory
     */
    protected $templateFactory;

    /**
     * @param \Shippit\Shipping\Helper\Checkout                 $helper
     * @param \Magento\Framework\View\Element\TemplateFactory   $templateFactory
     */
    public function __construct(
        \Shippit\Shipping\Helper\Checkout $helper,
        \Magento\Framework\View\Element\TemplateFactory $templateFactory
    ) {
        $this->helper = $helper;
        $this->templateFactory = $templateFactory;
    }

    public function execute(EventObserver $observer)
    {
        if ($observer->getElementName() == 'order_shipping_view'
            && ($this->helper->isAuthorityToLeaveActive() || $this->helper->isDeliveryInstructionsActive())
            ) {
            $orderShippingViewBlock = $observer->getLayout()
                ->getBlock($observer->getElementName());

            $order = $orderShippingViewBlock->getOrder();

            $shippitOrderOptions = $this->templateFactory->create();

            // Set the data for the block
            $shippitOrderOptions->setHelper($this->helper)
                ->setShippitDeliveryInstructions($order->getShippitDeliveryInstructions())
                ->setShippitAuthorityToLeave($order->getShippitAuthorityToLeave());

            $shippitOrderOptions->setTemplate('Shippit_Shipping::order/view/shippit_order_options.phtml');

            // Append to the blocks output and set
            $html = $observer->getTransport()->getOutput() . $shippitOrderOptions->toHtml();
            $observer->getTransport()->setOutput($html);
        }
    }
}
