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

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Magento\Framework\DataObject;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Type\AbstractType as ProductTypeAbstract;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProductType;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Store\Model\ScopeInterface;

class Shippit extends AbstractCarrierOnline implements CarrierInterface
{
    const NOTICE_MODULE_DISABLED = 'Skipping Live Quote - The Module is not enabled';
    const NOTICE_NOMETHODS_SELECTED = 'Skipping Live Quote - No Shipping Methods are selected';
    const NOTICE_PRODUCTS_NOT_ELIGIBLE = 'Skipping Live Quote - The cart contains items not eligable for shipping';

    /**
     * @var string
     */
    protected $_code = \Shippit\Shipping\Helper\Data::CARRIER_CODE;

    /**
     * @var \Shippit\Shipping\Helper\Carrier\Shippit
     */
    protected $helper;

    /**
     * @var \Shippit\Shipping\Helper\Sync\Order\Items
     */
    protected $itemsHelper;

    /**
     * @var \Shippit\Shipping\Helper\Api
     */
    protected $api;

    /**
     * @var \Shippit\Shipping\Model\Config\Source\Shippit\Shipping\QuoteMethods
     */
    protected $methods;

    /**
     * @var \Shippit\Shipping\Api\Request\QuoteInterface
     */
    protected $quote;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Repository
     */
    protected $productAttributeRepository;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Magento\Framework\Logger\Monolog $logger
     * @param \Magento\Framework\Xml\Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Shippit\Shipping\Helper\Carrier\Shippit $helper
     * @param \Shippit\Shipping\Helper\Api $api
     * @param \Shippit\Shipping\Model\Config\Source\Shippit\Shipping\QuoteMethods $methods
     * @param \Shippit\Shipping\Api\Request\QuoteInterface $quote
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Magento\Framework\Logger\Monolog $logger,
        \Magento\Framework\Xml\Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Shippit\Shipping\Helper\Carrier\Shippit $helper,
        \Shippit\Shipping\Helper\Api $api,
        \Shippit\Shipping\Helper\Sync\Order\Items $itemsHelper,
        \Shippit\Shipping\Model\Config\Source\Shippit\Shipping\QuoteMethods $methods,
        \Shippit\Shipping\Api\Request\QuoteInterface $quote,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->itemsHelper = $itemsHelper;
        $this->api = $api;
        $this->methods = $methods;
        $this->quote = $quote;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->scopeConfig = $scopeConfig;

        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
    }

    /**
     * Processing additional validation to check is carrier applicable.
     *
     * Adds backwards compatability for v2.2.5 and earlier
     * @url https://github.com/magento/magento2/issues/19592
     * @url https://github.com/magento/magento2/pull/16414
     *
     * @param \Magento\Framework\DataObject $request
     * @return $this|bool|\Magento\Framework\DataObject
     * @deprecated
     */
    public function proccessAdditionalValidation(DataObject $request)
    {
        return $this->processAdditionalValidation($request);
    }

    /**
     * Processing additional validation to check is carrier applicable.
     *
     * Workaround and handling for a Magento Core Bugs (MAGETWO-42591 & MAGETWO-55117)
     * @url https://github.com/magento/magento2/issues/1779
     * @url https://github.com/magento/magento2/issues/3789
     *
     * @param \Magento\Framework\DataObject $request
     * @return $this|bool|\Magento\Framework\DataObject
     */
    public function processAdditionalValidation(DataObject $request)
    {
        $country = $request->getDestCountryId();
        $state = $request->getDestRegionCode();
        $postcode = $request->getDestPostcode();
        $suburb = $request->getDestCity();

        // If the country is AU, state + postcode + suburb are required
        if (
            $country == 'AU'
            && (
                empty($state) || empty($postcode) || empty($suburb)
            )
        ) {
            return false;
        }
        // Otherwise, for non-AU destinations, postcode and suburb are required
        elseif (
            $country != 'AU'
            && (
                empty($postcode) || empty($suburb)
            )
        ) {
            return false;
        }

        return $this;
    }

    protected function _doShipmentRequest(DataObject $request)
    {
        $result = new DataObject();

        return $result;
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        // check if the module is active
        if (!$this->helper->isActive()) {
            $this->_logger->debug(self::NOTICE_MODULE_DISABLED);

            return false;
        }

        // check if we have any methods allowed before proceeding
        $allowedMethods = $this->helper->getAllowedMethods();
        if (count($allowedMethods) == 0) {
            $this->_logger->debug(self::NOTICE_NOMETHODS_SELECTED);

            return false;
        }

        // check the products are eligible for shippit shipping
        if (!$this->_canShipProducts($request)) {
            $this->_logger->debug(self::NOTICE_PRODUCTS_NOT_ELIGIBLE);

            return false;
        }

        $quoteRequest = $this->quote;

        // Get the first available dates based on the customer's shippit profile settings
        $quoteRequest->setOrderDate('');

        if ($request->getDestCountryId()) {
            $quoteRequest->setDropoffCountryCode($request->getDestCountryId());
        }

        if ($request->getShipperAddressStreet()) {
            $quoteRequest->setDropoffAddress($request->getShipperAddressStreet());
        }

        if ($request->getDestStreet()) {
            // Replace any newline characters in the street address with comma + space
            $streetAddress = str_replace("\n", ', ', $request->getDestStreet());

            $quoteRequest->setDropoffAddress($streetAddress);
        }

        $quoteRequest->setDropoffPostcode($request->getDestPostcode());
        $quoteRequest->setDropoffState($request->getDestRegionCode());
        $quoteRequest->setDropoffSuburb($request->getDestCity());
        $quoteRequest->setParcelAttributes($this->_getParcelAttributes($request));
        $storeCountryCode = $this->scopeConfig->getValue(
            'general/country/default',
            ScopeInterface::SCOPE_WEBSITES
        );

        // @Workaround
        // - Only add the dutiable_amount for domestic orders
        // - The Shippit Quotes API does not currently support the dutiable_amount
        //   field being present for domestic deliveries — declaring a dutiable
        //   amount value for these quotes may result in some carrier quotes not
        //   being available.
        if ($storeCountryCode != $request->getDestCountryId()) {
            $quoteRequest->setDutiableAmount($this->_getDutiableAmount($request));
        }

        try {
            // Call the api and retrieve the quote
            $shippingQuotes = $this->api->createQuote($quoteRequest);
        }
        catch (\Exception $e) {
            $this->_logger->error('Quote Request Error - ' . $e->getMessage());

            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $rateResult */
        $rateResult = $this->_rateFactory->create();

        $this->_processShippingQuotes($rateResult, $shippingQuotes);

        return $rateResult;
    }

    public function getCarrierCode()
    {
        return $this->_code;
    }

    protected function _processShippingQuotes(&$rateResult, $shippingQuotes)
    {
        $allowedMethods = $this->helper->getAllowedMethods();

        $isOnDemandAvailable = in_array('on_demand', $allowedMethods);
        $isPriorityAvailable = in_array('priority', $allowedMethods);
        $isExpressAvailable = in_array('express', $allowedMethods);
        $isStandardAvailable = in_array('standard', $allowedMethods);

        // Process the response and return available options
        foreach ($shippingQuotes as $shippingQuoteKey => $shippingQuote) {
            if ($shippingQuote->success) {
                switch ($shippingQuote->service_level) {
                    case 'on_demand':
                        if ($isOnDemandAvailable) {
                            $this->_addOnDemandQuote($rateResult, $shippingQuote);
                        }

                        break;
                    case 'priority':
                        if ($isPriorityAvailable) {
                            $this->_addPriorityQuote($rateResult, $shippingQuote);
                        }

                        break;
                    case 'express':
                        if ($isExpressAvailable) {
                            $this->_addExpressQuote($rateResult, $shippingQuote);
                        }

                        break;
                    case 'standard':
                        if ($isStandardAvailable) {
                            $this->_addStandardQuote($rateResult, $shippingQuote);
                        }

                        break;
                }
            }
        }

        return $rateResult;
    }

    protected function _addStandardQuote(&$rateResult, $shippingQuote)
    {
        foreach ($shippingQuote->quotes as $shippingQuoteQuote) {
            $rateResultMethod = $this->_rateMethodFactory->create();
            $rateResultMethod->setCarrier($this->_code)
                ->setCarrierTitle($this->helper->getTitle())
                ->setMethod('Standard')
                ->setMethodTitle('Standard')
                ->setCost($shippingQuoteQuote->price)
                ->setPrice($this->_getQuotePrice($shippingQuoteQuote->price));

            $rateResult->append($rateResultMethod);
        }
    }

    protected function _addExpressQuote(&$rateResult, $shippingQuote)
    {
        foreach ($shippingQuote->quotes as $shippingQuoteQuote) {
            $rateResultMethod = $this->_rateMethodFactory->create();
            $rateResultMethod->setCarrier($this->_code)
                ->setCarrierTitle($this->helper->getTitle())
                ->setMethod('Express')
                ->setMethodTitle('Express')
                ->setCost($shippingQuoteQuote->price)
                ->setPrice($this->_getQuotePrice($shippingQuoteQuote->price));

            $rateResult->append($rateResultMethod);
        }
    }

    protected function _addPriorityQuote(&$rateResult, $shippingQuote)
    {
        $maxTimeslots = $this->helper->getMaxTimeslots();
        $timeslotCount = 0;

        foreach ($shippingQuote->quotes as $shippingQuoteQuote) {
            if (!empty($maxTimeslots) && $maxTimeslots <= $timeslotCount) {
                break;
            }

            $rateResultMethod = $this->_rateMethodFactory->create();

            if (property_exists($shippingQuoteQuote, 'delivery_date')
                && property_exists($shippingQuoteQuote, 'delivery_window')
                && property_exists($shippingQuoteQuote, 'delivery_window_desc')) {
                $timeslotCount++;
                $carrierTitle = $this->helper->getTitle();
                $method = 'Priority' . '_' . $shippingQuoteQuote->delivery_date . '_' . $shippingQuoteQuote->delivery_window;
                $methodTitle = 'Priority' . ' - Delivered ' . $shippingQuoteQuote->delivery_date . ', Between ' . $shippingQuoteQuote->delivery_window_desc;
            }
            else {
                $carrierTitle = $this->helper->getTitle();
                $method = 'Priority';
                $methodTitle = 'Priority';
            }

            $rateResultMethod->setCarrier($this->_code)
                ->setCarrierTitle($carrierTitle)
                ->setMethod($method)
                ->setMethodTitle($methodTitle)
                ->setCost($shippingQuoteQuote->price)
                ->setPrice($this->_getQuotePrice($shippingQuoteQuote->price));

            $rateResult->append($rateResultMethod);
        }
    }

    protected function _addOnDemandQuote(&$rateResult, $shippingQuote)
    {
        $maxTimeslots = $this->helper->getMaxTimeslots();
        $timeslotCount = 0;

        foreach ($shippingQuote->quotes as $shippingQuoteQuote) {
            if (!empty($maxTimeslots) && $maxTimeslots <= $timeslotCount) {
                break;
            }

            // Parse the estimated_delivery_time received from Shippit to
            // obtain a human readable delivery estimate
            $deliveryEstimate = Carbon::parse($shippingQuoteQuote->estimated_delivery_time)
                ->diffForHumans(
                    [
                        'options' => Carbon::CEIL,
                        'parts' => 1,
                    ],
                    CarbonInterface::DIFF_ABSOLUTE
                );

            $rateResultMethod = $this->_rateMethodFactory->create();

            $carrierTitle = $this->helper->getTitle();
            $method = 'ondemand';
            $methodTitle = sprintf(
                'On Demand - Delivered within %s',
                $deliveryEstimate
            );

            $rateResultMethod->setCarrier($this->_code)
                ->setCarrierTitle($carrierTitle)
                ->setMethod($method)
                ->setMethodTitle($methodTitle)
                ->setCost($shippingQuoteQuote->price)
                ->setPrice($this->_getQuotePrice($shippingQuoteQuote->price));

            $rateResult->append($rateResultMethod);
        }
    }

    /**
     * Get the quote price, including the margin amount if enabled
     * @param  float $quotePrice The quote amount
     * @return float             The quote amount, with margin if applicable
     */
    protected function _getQuotePrice($quotePrice)
    {
        switch ($this->helper->getMargin()) {
            case 'fixed':
                $quotePrice += (float) $this->helper->getMarginAmount();
                break;
            case 'percentage':
                $quotePrice *= (1 + ( (float) $this->helper->getMarginAmount() / 100));
                break;
        }

        // ensure we get the lowest price, but not below 0.
        $quotePrice = max(0, $quotePrice);

        return $quotePrice;
    }

    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return \Magento\Shipping\Model\Tracking\Result
     */
    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        $result = $this->_trackFactory->create();

        foreach ($trackings as $tracking) {
            $trackStatus = $this->_trackStatusFactory->create();

            $trackStatus->setCarrier($this->_code)
                ->setCarrierTitle($this->getConfigData('title'))
                ->setUrl('https://www.shippit.com/track/' . $tracking)
                ->setTracking($tracking);

            $result->append($trackStatus);
        }

        return $result;
    }

    /**
     * Get the allowed shipping methods,
     * based on the available methods in Shippit's systems
     * and the user's configured options
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $configAllowedMethods = $this->helper->getAllowedMethods();

        $availableMethods = $this->methods->toArray();

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

        return parent::isZipCodeRequired($countryId);
    }

    /**
     * Checks the request and ensures all products are either enabled, or part of the attributes elidgable
     *
     * @param \Magento\Framework\DataObject $request
     * @return bool
     */
    protected function _canShipProducts($request)
    {
        $items = $request->getAllItems();
        $productIds = [];

        foreach ($items as $item) {
            // Skip special product types
            if ($item->getProduct()->getTypeId() == ConfigurableProductType::TYPE_CODE
                || $item->getProduct()->getTypeId() == GroupedProductType::TYPE_CODE
                || $item->getProduct()->getTypeId() == ProductType::TYPE_BUNDLE
                || $item->getProduct()->getTypeId() == ProductType::TYPE_VIRTUAL) {
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

    protected function _canShipEnabledProducts($productIds)
    {
        if (!$this->helper->isEnabledProductActive()) {
            return true;
        }

        $enabledProductIds = $this->helper->getEnabledProductIds();

        // if we have enabled products, check that all
        // items in the shipping request are enabled
        if (count($enabledProductIds) > 0) {
            if ($productIds != array_intersect($productIds, $enabledProductIds)) {
                return false;
            }
        }

        return true;
    }

    protected function _canShipEnabledProductAttributes($productIds)
    {
        if (!$this->helper->isEnabledProductAttributeActive()) {
            return true;
        }

        $attributeCode = $this->helper->getEnabledProductAttributeCode();
        $attributeValue = $this->helper->getEnabledProductAttributeValue();

        if (!empty($attributeCode) && !empty($attributeValue)) {
            $attributeProductCount = $this->productCollectionFactory->create();
            $attributeProductCount->addAttributeToFilter('entity_id', ['in' => $productIds]);

            $attributeInputType = $this->productAttributeRepository
                ->get($attributeCode)
                ->getFrontendInput();

            if ($attributeInputType == 'select' || $attributeInputType == 'multiselect') {
                // Attempt to filter items by the select / multiselect instance
                $attributeProductCount = $this->_filterByAttributeOptionId($attributeProductCount, $attributeCode, $attributeValue);
            }
            else {
                $attributeProductCount = $this->_filterByAttributeValue($attributeProductCount, $attributeCode, $attributeValue);
            }

            $attributeProductCount = $attributeProductCount->getSize();

            // If the number of filtered products is not
            // equal to the products in the cart, return false
            if ($attributeProductCount != count($productIds)) {
                return false;
            }
        }

        // All checks have passed, return true
        return true;
    }

    protected function _filterByAttributeOptionId($collection, $attributeCode, $attributeValue)
    {
        $attributeOptions = $this->productAttributeRepository
            ->get($attributeCode)
            ->getSource();

        $attributeOptionIds = $this->_getAllAttributeOptionIds($attributeOptions);

        if (strpos($attributeValue, '*') !== FALSE) {
            $attributeOptions = $attributeOptions->getAllOptions();
            $pattern = preg_quote($attributeValue, '/');
            $pattern = str_replace('\*', '.*', $pattern);
            $attributeOptionIds = [];

            foreach ($attributeOptions as $attributeOption) {
                if (preg_match('/^' . $pattern . '$/i', $attributeOption['label'])) {
                    $attributeOptionIds[] = $attributeOption['value'];
                }
            }
        }
        else {
            $attributeOptions = $attributeOptions->getOptionId($attributeValue);
            $attributeOptionIds = [$attributeOptions];
        }

        // if we have no options that match the filter,
        // avoid filtering and return early.
        if (empty($attributeOptionIds)) {
            return $collection;
        }

        return $collection->addAttributeToFilter(
            $attributeCode,
            ['in' => $attributeOptionIds]
        );
    }

    protected function _filterByAttributeValue($collection, $attributeCode, $attributeValue)
    {
        // Convert the attribute value with "*" to replace with a mysql wildcard character
        $attributeValue = str_replace('*', '%', $attributeValue);

        return $collection->addAttributeToFilter(
            $attributeCode,
            ['like' => $attributeValue]
        );
    }

    protected function _getAllAttributeOptionIds($attributeOptions)
    {
        $attributeOptionIds = [];

        foreach ($attributeOptions as $attributeOption) {
            if ($attributeOption['value']) {
                $attributeOptionIds[] = $attributeOption['value'];
            }
        }

        return $attributeOptionIds;
    }

    protected function _getParcelAttributes($request)
    {
        $items = $request->getAllItems();
        $parcelAttributes = [];

        foreach ($items as $item) {
            if (!$this->canAddItemToQuote($item)) {
                continue;
            }

            $newParcel = [
                'qty' => $this->getItemQty($item),
                'weight' => ($item->getWeight() ? $item->getWeight() : 0.2),
            ];

            $length = $this->getItemLength($item);
            $width = $this->getItemWidth($item);
            $depth = $this->getItemDepth($item);

            // for dimensions, ensure the item has values for all dimensions
            if (!empty($length) && !empty($width) && !empty($depth)) {
                $newParcel['length'] = (float) $length;
                $newParcel['width'] = (float) $width;
                $newParcel['depth'] = (float) $depth;
            }

            $parcelAttributes[] = $newParcel;
        }

        return $parcelAttributes;
    }

    /**
     * Retrieves a item's ordered quantity
     * - This is useful due to Magento handling the orddered qty differently,
     *   based on the item type and it's shipping configuration
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return float|int
     */
    protected function getItemQty($item)
    {
        // If the item has a parent, consider it's type and structure to determine an appropriate qty
        if ($item->getParentItem()) {
            $parentItem = $this->_getRootItem($item);

            // If the product is a configurable product, use the configurable item qty
            if ($parentItem->getProductType() == ConfigurableProductType::TYPE_CODE) {
                return $parentItem->getQty();
            }

            // If the product is a bundle product and the bundle items are being
            // shipped seperately, use the bundle items qty * subitems qty
            if (
                $parentItem->getProductType() == ProductType::TYPE_BUNDLE
                && $parentItem->isShipSeparately()
            ) {
                return ($item->getQty() * $parentItem->getQty());
            }
        }

        // Otherwise, use the qty of the items
        return $item->getQty();
    }

    protected function _getDutiableAmount($request)
    {
        // Get the discounted value of the package
        // ie: The actual order value as paid by the customer
        return $request->getPackageValueWithDiscount();
    }

    /*
        Check if item is elligible to be added for the quote request
        based on various product type and option conditions
     */
    protected function canAddItemToQuote($item)
    {
        // If item is virtual return early
        if ($item->getIsVirtual()) {
            return false;
        }

        $rootItem = $this->_getRootItem($item);

        // Always true if item product type is simple with no parent
        if (empty($item->getParentItemId()) && $item->getProductType() == ProductType::TYPE_SIMPLE) {
            return true;
        }
        // Always true if item product type is a grouped product
        elseif ($rootItem->getProductType() == GroupedProductType::TYPE_CODE) {
            return true;
        }
        // If the product is a bundle, check if it's shipped together or seperately...
        elseif ($rootItem->getProductType() == ProductType::TYPE_BUNDLE) {
            // If the bundle is being shipped seperately
            if ($rootItem->getProduct()->getShipmentType() == ProductTypeAbstract::SHIPMENT_SEPARATELY) {
                // Check if this is the bundle item, or the item within the bundle
                // If it's the bundle item
                if ($item->getId() == $rootItem->getId()) {
                    return false;
                }
                // Otherewise, if it's the child item of a shipped seperately bundle
                else {
                    return true;
                }
            }
            else {
                // Check if this is the bundle item, or the item within the bundle
                // If it's the bundle item
                if ($item->getId() == $rootItem->getId()) {
                    return true;
                }
                // Otherewise, if it's the child item of a shipped together bundle
                else {
                    return false;
                }
            }
        }
        // If the product is a configurable product
        elseif ($rootItem->getProductType() == ConfigurableProductType::TYPE_CODE) {
            // Check if the item is a parent / child and return accordingly
            if ($item->getId() == $rootItem->getId()) {
                return false;
            }
            else {
                return true;
            }
        }
    }

    protected function getItemLength($item)
    {
        if (!$this->itemsHelper->isProductDimensionActive()) {
            return;
        }

        return $this->helper->getLength($item);
    }

    protected function getItemWidth($item)
    {
        if (!$this->itemsHelper->isProductDimensionActive()) {
            return;
        }

        return $this->helper->getWidth($item);
    }

    protected function getItemDepth($item)
    {
        if (!$this->itemsHelper->isProductDimensionActive()) {
            return;
        }

        return $this->helper->getDepth($item);
    }

    protected function _getRootItem($item)
    {
        if ($item->getParentItem()) {
            return $item->getParentItem();
        }
        else {
            return $item;
        }
    }
}
