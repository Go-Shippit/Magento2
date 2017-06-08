<?php
namespace Shippit\Shipping\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddHtmlToOrderShippingViewObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function execute(EventObserver $observer)
    {
        if ($observer->getElementName() == 'order_shipping_view') {
            $orderShippingViewBlock = $observer->getLayout()->getBlock($observer->getElementName());
            $order = $orderShippingViewBlock->getOrder();
            $orderOptionsBlock = $this->objectManager->create('Magento\Framework\View\Element\Template');
            $orderOptionsBlock->setShippitDeliveryInstructions($order->getShippitDeliveryInstructions());
            $orderOptionsBlock->setShippitAuthorityToLeave($order->getShippitAuthorityToLeave());
            $orderOptionsBlock->setTemplate('Shippit_Shipping::order/view/shippit_order_options.phtml');
            $html = $observer->getTransport()->getOutput() . $orderOptionsBlock->toHtml();
            $observer->getTransport()->setOutput($html);
        }
    }
}
