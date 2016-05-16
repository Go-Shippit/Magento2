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

namespace Shippit\Shipping\Model\Carrier;
 
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
// use Magento\Catalog\Model\Product\Type;
 
class Shippit extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    const NOTICE_MODULE_DISABLED = 'Skipping Live Quote - The Module is not enabled';
    const NOTICE_NOMETHODS_SELECTED = 'Skipping Live Quote - No Shipping Methods are selected';
    const NOTICE_PRODUCTS_NOT_ELIGIBLE = 'Skipping Live Quote - The cart contains items not eligable for shipping';

    /**
     * @var string
     */
    protected $_code = \Shippit\Shipping\Helper\Data::CARRIER_CODE;

    protected $_helper;
    protected $_api;
    protected $_methods;
    protected $_quote;

    protected $_rateResultFactory;
    protected $_rateMethodFactory;
 
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
     * @param \Shippit\Shipping\Logger\Logger                             $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Shippit\Shipping\Helper\Data                               $helper
     * @param \Shippit\Shipping\Helper\Api                                $api
     * @param \Shippit\Shipping\Model\Config\Source\Shippit\Methods       $methods
     * @param \Shippit\Shipping\Api\Request\QuoteInterface                $quote
     * @param array                                                       $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Shippit\Shipping\Logger\Logger $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Shippit\Shipping\Helper\Carrier $helper,
        \Shippit\Shipping\Helper\Api $api,
        \Shippit\Shipping\Model\Config\Source\Shippit\Methods $methods,
        \Shippit\Shipping\Api\Request\QuoteInterface $quote,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;

        $this->_helper = $helper;
        $this->_api = $api;
        $this->_methods = $methods;
        $this->_quote = $quote;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }
 
    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        // check if the module is active
        if (!$this->_helper->isActive()) {
            $this->_logger->addDebug(self::NOTICE_MODULE_DISABLED);

            return false;
        }

        // check if we have any methods allowed before proceeding
        $allowedMethods = $this->_helper->getAllowedMethods();
        if (count($allowedMethods) == 0) {
            $this->_logger->addDebug(self::NOTICE_NOMETHODS_SELECTED);

            return false;
        }

        // check the products are eligible for shippit shipping
        if (!$this->_canShipProducts($request)) {
            $this->_logger->addDebug(self::NOTICE_PRODUCTS_NOT_ELIGIBLE);
            return false;
        }
        
        $quoteRequest = $this->_quote;

        // Get the first available dates based on the customer's shippit profile settings
        $quoteRequest->setOrderDate('');

        if ($request->getShipperAddressStreet()) {
            $quoteRequest->setDropoffSuburb($request->getShipperAddressStreet());
        }

        if ($request->getDestPostcode()) {
            $quoteRequest->setDropoffPostcode($request->getDestPostcode());
        }

        $quoteRequest->setDropoffState($request->getDestRegionCode());
        $quoteRequest->setDropoffSuburb($request->getDestCity());
        $quoteRequest->setParcelAttributes($this->_getParcelAttributes($request));

        try {
            // Call the api and retrieve the quote
            $shippingQuotes = $this->_api->getQuote($quoteRequest);
        }
        catch (\Exception $e) {
            $this->_logger->addError('Quote Request Error - ' . $e->getMessage());
        
            return false;
        }
        
        /** @var \Magento\Shipping\Model\Rate\Result $rateResult */
        $rateResult = $this->_rateResultFactory->create();

        $this->_processShippingQuotes($rateResult, $shippingQuotes);

        return $rateResult;
    }

    /**
     * Do request to shipment
     * Implementation must be in overridden method
     *
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     * @api
     */
    public function requestToShipment($request)
    {
        // @todo - implement request to shipment
    }

    public function getCarrierCode()
    {
        return $this->_code;
    }

    private function _processShippingQuotes(&$rateResult, $shippingQuotes)
    {
        $allowedMethods = $this->_helper->getAllowedMethods();

        $isPremiumAvailable = in_array('premium', $allowedMethods);
        $isExpressAvailable = in_array('express', $allowedMethods);
        $isStandardAvailable = in_array('standard', $allowedMethods);

        // Process the response and return available options
        foreach ($shippingQuotes as $shippingQuoteKey => $shippingQuote) {
            if ($shippingQuote->success) {
                if ($shippingQuote->courier_type == 'Bonds'
                    && $isPremiumAvailable) {
                    $this->_addPremiumQuote($rateResult, $shippingQuote);
                }
                elseif ($shippingQuote->courier_type == 'eparcelexpress'
                    && $isExpressAvailable) {
                    $this->_addExpressQuote($rateResult, $shippingQuote);
                }
                elseif ($isStandardAvailable) {
                    $this->_addStandardQuote($rateResult, $shippingQuote);
                }
            }
        }

        return $rateResult;
    }

    private function _addStandardQuote(&$rateResult, $shippingQuote)
    {
        foreach ($shippingQuote->quotes as $shippingQuoteQuote) {
            $rateResultMethod = $this->_rateMethodFactory->create();
            $rateResultMethod->setCarrier($this->_code)
                ->setCarrierTitle($this->_helper->getTitle())
                ->setMethod('Standard')
                ->setMethodTitle('Standard')
                ->setCost($shippingQuoteQuote->price)
                ->setPrice($shippingQuoteQuote->price);

            $rateResult->append($rateResultMethod);
        }
    }

    private function _addExpressQuote(&$rateResult, $shippingQuote)
    {
        foreach ($shippingQuote->quotes as $shippingQuoteQuote) {
            $rateResultMethod = $this->_rateMethodFactory->create();
            $rateResultMethod->setCarrier($this->_code)
                ->setCarrierTitle($this->_helper->getTitle())
                ->setMethod('Express')
                ->setMethodTitle('Express')
                ->setCost($shippingQuoteQuote->price)
                ->setPrice($shippingQuoteQuote->price);

            $rateResult->append($rateResultMethod);
        }
    }

    private function _addPremiumQuote(&$rateResult, $shippingQuote)
    {
        $maxTimeslots = $this->_helper->getMaxTimeslots();
        $timeslotCount = 0;

        foreach ($shippingQuote->quotes as $shippingQuoteQuote) {
            if (!empty($maxTimeslots)&& $maxTimeslots <= $timeslotCount) {
                break;
            }

            $rateResultMethod = $this->_rateMethodFactory->create();

            if (property_exists($shippingQuoteQuote, 'delivery_date')
                && property_exists($shippingQuoteQuote, 'delivery_window')
                && property_exists($shippingQuoteQuote, 'delivery_window_desc')) {
                $timeslotCount++;
                $carrierTitle = $this->_helper->getTitle();
                $method = $shippingQuote->courier_type . '_' . $shippingQuoteQuote->delivery_date . '_' . $shippingQuoteQuote->delivery_window;
                $methodTitle = 'Premium' . ' - Delivered ' . $shippingQuoteQuote->delivery_date. ', Between ' . $shippingQuoteQuote->delivery_window_desc;
            }
            else {
                $carrierTitle = $this->_helper->getTitle();
                $method = 'Premium';
                $methodTitle = 'Premium';
            }

            $rateResultMethod->setCarrier($this->_code)
                ->setCarrierTitle($carrierTitle)
                ->setMethod($method)
                ->setMethodTitle($methodTitle)
                ->setCost($shippingQuoteQuote->price)
                ->setPrice($shippingQuoteQuote->price);

            $rateResult->append($rateResultMethod);
        }
    }

    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * @TODO: convert to magento2 method
     *
     * Get the tracking details for the shipping method
     * @param  string $tracking The tracking reference
     * @return [type]           [description]
     */
    public function getTrackingInfo($tracking)
    {
        // $track = Mage::getModel('shipping/tracking_result_status');
        // $track->setUrl('https://www.shippit.com/track/' . $tracking)
        //     ->setTracking($tracking)
        //     ->setCarrierTitle($this->getConfigData('name'));

        // return $track;
    }

    /**
     * Get the allowed shipping methods,
     * based on the available methods in Shippit's systems
     * and the user's configured options
     *
     * @return array key => value array of allowed methods
     */
    public function getAllowedMethods()
    {
        $configAllowedMethods = $this->_helper->getAllowedMethods();
        
        $availableMethods = $this->_methods->toArray();

        $allowedMethods = [];

        foreach ($availableMethods as $methodValue => $methodLabel) {
            if (in_array($methodValue, $configAllowedMethods)) {
                $allowedMethods[$methodValue] = $methodLabel;
            }
        }

        return $allowedMethods;
    }

    public function isStateProvinceRequired()
    {
        return true;
    }

    public function isCityRequired()
    {
        return true;
    }

    public function isZipCodeRequired($countryId = null)
    {
        if ($countryId == 'AU') {
            return true;
        }
        else {
            return parent::isZipCodeRequired($countryId);
        }
    }

    /**
     * Checks the request and ensures all products are either enabled, or part of the attributes elidgable
     *
     * @param  [type] $request The shipment request
     * @return boolean         True or false
     */
    private function _canShipProducts($request)
    {
        $items = $request->getAllItems();
        $productIds = [];

        foreach ($items as $item) {
            // Skip special product types
            if ($item->getProduct()->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
                || $item->getProduct()->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE
                || $item->getProduct()->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
                || $item->getProduct()->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL) {
                continue;
            }

            $productIds[] = $item->getProduct()->getId();
        }

        $canShipEnabledProducts = $this->_canShipEnabledProducts($productIds);
        $canShipEnabledProductAttributes = $this->_canShipEnabledProductAttributes($productIds);

        if ($canShipEnabledProducts && $canShipEnabledProductAttributes) {
            return true;
        }
        else {
            return false;
        }
    }

    private function _canShipEnabledProducts($productIds)
    {
        if (!$this->_helper->isEnabledProductActive()) {
            return true;
        }

        $enabledProductIds = $this->_helper->getEnabledProductIds();

        // if we have enabled products, check that all
        // items in the shipping request are enabled
        if (count($enabledProductIds) > 0) {
            if ($productIds != array_intersect($productIds, $enabledProductIds)) {
                return false;
            }
        }

        return true;
    }

    private function _canShipEnabledProductAttributes($productIds)
    {
        if (!$this->_helper->isEnabledProductAttributeActive()) {
            return true;
        }

        $attributeCode = $this->_helper->getEnabledProductAttributeCode();
        $attributeValue = $this->_helper->getEnabledProductAttributeValue();
        
        if (!empty($attributeCode) && !empty($attributeValue)) {
            $attributeProductCount = $this->_product
                ->getCollection()
                ->addAttributeToFilter('entity_id', ['in' => $productIds]);

            // When filtering by attribute value, allow for * as a wildcard
            if (strpos($attributeValue, '*') !== FALSE) {
                $attributeValue = str_replace('*', '%', $attributeValue);

                $attributeProductCount = $attributeProductCount->addAttributeToFilter($attributeCode, ['like' => $attributeValue])
                    ->getSize();
            }
            // Otherwise, use the exact match
            else {
                $attributeProductCount = $attributeProductCount->addAttributeToFilter($attributeCode, ['eq' => $attributeValue])
                    ->getSize();
            }

            // If the number of filtered products is not
            // equal to the products in the cart, return false
            if ($attributeProductCount != count($productIds)) {
                return false;
            }
        }

        // All checks have passed, return true
        return true;
    }

    private function _getParcelAttributes($request)
    {
        $items = $request->getAllItems();
        $parcelAttributes = [];

        foreach ($items as $item) {
            // Skip special product types
            if ($item->getProduct()->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
                $parcelAttributes[] = [
                    'qty' => $item->getQty(),
                    'weight' => $item->getWeight()
                ];
            }
        }

        return $parcelAttributes;
    }
}