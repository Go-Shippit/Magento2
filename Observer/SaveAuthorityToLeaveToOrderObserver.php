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

class SaveAuthorityToLeaveToOrderObserver implements ObserverInterface
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

    public function execute(EventObserver $observer)
    {
        if (!$this->helper->isAuthorityToLeaveActive()) {
            return $this;
        }

        $order = $observer->getOrder();
        $quote = $this->quoteRepository->get($order->getQuoteId());
        $order->setShippitAuthorityToLeave($quote->getShippitAuthorityToLeave());

        return $this;
    }
}