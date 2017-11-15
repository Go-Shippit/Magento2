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

namespace Shippit\Shipping\Plugin\Checkout;

class QuoteDeliveryInstructionsPlugin
{
    /**
     * @var \Shippit\Shipping\Helper\Checkout
     */
    protected $helper;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @param \Shippit\Shipping\Helper\Checkout         $helper
     * @param \Magento\Quote\Model\QuoteRepository      $quoteRepository
     */
    public function __construct(
        \Shippit\Shipping\Helper\Checkout $helper,
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->helper = $helper;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        if (!$this->helper->isDeliveryInstructionsActive()) {
            return;
        }

        $extensionAttributes = $addressInformation->getExtensionAttributes();
        $deliveryInstructions = $extensionAttributes->getShippitDeliveryInstructions();

        $quote = $this->quoteRepository->getActive($cartId);

        $quote->setShippitDeliveryInstructions($deliveryInstructions);
    }
}
