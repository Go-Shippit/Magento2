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

namespace Shippit\Shipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;

class ClickAndCollect extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = \Shippit\Shipping\Helper\Data::CARRIER_CODE_CC;

    protected $_helper;
    protected $_rateResultFactory;
    protected $_rateMethodFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Magento\Framework\Logger\Monolog $logger
     * @param \Shippit\Shipping\Helper\Carrier\ClickAndCollect $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Magento\Framework\Logger\Monolog $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Shippit\Shipping\Helper\Carrier\ClickAndCollect $helper,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_helper = $helper;

        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $data
        );
    }

    public function isTrackingAvailable()
    {
        return false;
    }

    public function getAllowedMethods()
    {
        $allowedMethods = [
            $this->_code => $this->getConfigData('title'),
        ];

        return $allowedMethods;
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        // check if the Shippit module is active
        if (!$this->_helper->isActive()) {
            return false;
        }

        $rateResult = $this->_rateResultFactory->create();

        $rateResult->append($this->_getClickAndCollectRate());

        return $rateResult;
    }

    protected function _getClickAndCollectRate()
    {
        $rateResultMethod = $this->_rateMethodFactory->create();
        $rateResultMethod->setCarrier($this->_code);
        $rateResultMethod->setCarrierTitle($this->getConfigData('title'));
        $rateResultMethod->setMethod($this->_code);
        $rateResultMethod->setMethodTitle($this->getConfigData('method'));
        $rateResultMethod->setPrice(0);
        $rateResultMethod->setCost(0);

        return $rateResultMethod;
    }
}
