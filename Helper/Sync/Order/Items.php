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

namespace Shippit\Shipping\Helper\Sync\Order;

use Magento\Store\Model\ScopeInterface;

class Items extends \Shippit\Shipping\Helper\Sync\Order
{
    const UNIT_DIMENSION_MILLIMETRES = 'millimetres';
    const UNIT_DIMENSION_CENTIMETRES = 'centimetres';
    const UNIT_DIMENSION_METRES = 'metres';

    const XML_PATH_SETTINGS = 'shippit/sync_item/';

    protected $_locationAttributeCode = null;

    /**
     * Return store config value for key
     *
     * @param   string $key
     * @return  string
     */
    public function getValue($key, $scope = ScopeInterface::SCOPE_STORES)
    {
        $path = self::XML_PATH_SETTINGS . $key;

        return $this->_scopeConfig->getValue($path, $scope);
    }

    /**
     * Begin System Configuration Helpers
     */

    public function isProductDimensionActive()
    {
        return $this->getValue('product_dimension_active');
    }

    public function getProductUnitDimension()
    {
        return $this->getValue('product_unit_dimension');
    }

    public function getProductDimensionLengthAttributeCode()
    {
        return $this->getValue('product_dimension_length_attribute_code');
    }

    public function getProductDimensionWidthAttributeCode()
    {
        return $this->getValue('product_dimension_width_attribute_code');
    }

    public function getProductDimensionDepthAttributeCode()
    {
        return $this->getValue('product_dimension_depth_attribute_code');
    }

    public function isProductLocationActive()
    {
        return $this->getValue('product_location_active');
    }

    public function getProductLocationAttributeCode()
    {
        return $this->getValue('product_location_attribute_code');
    }

    public function getProductTariffAttributeCode()
    {
        return $this->getValue('product_tariff_code');
    }

    /**
     * Begin Data Processing Helpers
     */

    public function getDimension($dimension)
    {
        // ensure the dimension is present and not empty
        if (empty($dimension)) {
            return;
        }

        switch ($this->getProductUnitDimension()) {
            case self::UNIT_DIMENSION_MILLIMETRES:
                $dimension = ($dimension / 1000);
                break;
            case self::UNIT_DIMENSION_CENTIMETRES:
                $dimension = ($dimension / 100);
                break;
            case self::UNIT_DIMENSION_METRES:
                $dimension = $dimension;
                break;
        }

        return (float) $dimension;
    }

    public function getWidth($item)
    {
        $attributeCode = $this->getProductDimensionWidthAttributeCode();

        if (empty($attributeCode)) {
            return;
        }

        $attributeValue = $this->getAttributeValue($item->getProduct(), $attributeCode);

        return $this->getDimension($attributeValue);
    }

    public function getLength($item)
    {
        $attributeCode = $this->getProductDimensionLengthAttributeCode();

        if (empty($attributeCode)) {
            return;
        }

        $attributeValue = $this->getAttributeValue($item->getProduct(), $attributeCode);

        return $this->getDimension($attributeValue);
    }

    public function getDepth($item)
    {
        $attributeCode = $this->getProductDimensionDepthAttributeCode();

        if (empty($attributeCode)) {
            return;
        }

        $attributeValue = $this->getAttributeValue($item->getProduct(), $attributeCode);

        return $this->getDimension($attributeValue);
    }

    public function getTariffCode($item)
    {
        $attributeCode = $this->getProductTariffAttributeCode();

        if (empty($attributeCode)) {
            return;
        }

        return $this->getAttributeValue($item->getProduct(), $attributeCode);
    }

    public function getSkus($items)
    {
        $itemsSkus = [];

        foreach ($items as $item) {
            if (isset($item['sku'])) {
                $itemSkus[] = $item['sku'];
            }
        }

        return $itemSkus;
    }

    public function getIds($items)
    {
        $itemsIds = [];

        foreach ($items as $item) {
            if (isset($item['id'])) {
                $itemsIds[] = $item['id'];
            }
        }

        return $itemsIds;
    }

    public function getQtyToShip($item, $qtyRequested = null)
    {
        $qtyToShip = $item->getQtyToShip();

        // if no quantity is provided, or the qty requested is
        // greater than the pending shipment qty
        // return the pending shipment qty
        if (empty($qtyRequested) || $qtyRequested > $qtyToShip) {
            return $qtyToShip;
        }
        // otherwise, return the qty requested
        else {
            return $qtyRequested;
        }
    }

    public function getItemData($items, $itemKey, $itemValue, $itemDataKey)
    {
        $searchResult = array_search($itemValue, array_column($items, $itemKey));

        if ($searchResult !== false) {
            return $items[$searchResult][$itemDataKey];
        }

        return false;
    }

    public function getLocation($item)
    {
        $attributeCode = $this->getLocationAttributeCode();

        if ($attributeCode) {
            return $this->getAttributeValue($item->getProduct(), $attributeCode);
        }
        else {
            return null;
        }
    }

    public function getLocationAttributeCode()
    {
        if (is_null($this->_locationAttributeCode)) {
            if (!$this->isProductLocationActive()) {
                $this->_locationAttributeCode = false;
            }
            else {
                $this->_locationAttributeCode = $this->getProductLocationAttributeCode();
            }
        }

        return $this->_locationAttributeCode;
    }

    /**
     * Get the product attribute value, ensuring we get
     * the full text value if it's a select or multiselect attribute
     *
     * @param  object  $product        The Product Object
     * @param  string  $attributeCode  The Attribute Code
     * @return string                  The Product Attribute Value (full text)
     */
    public function getAttributeValue($product, $attributeCode)
    {
        $attribute = $product->getResource()->getAttribute($attributeCode);

        if ($attribute && $attribute->usesSource()) {
            $attributeValue = $product->getAttributeText($attributeCode);
        }
        else {
            $attributeFunction = $this->getFunctionName($attributeCode);
            $attributeValue = $product->{$attributeFunction}();
        }

        return $attributeValue;
    }

    protected function getFunctionName($attributeCode, $prefix = 'get', $capitaliseFirstChar = true)
    {
        if ($capitaliseFirstChar) {
            $attributeCode[0] = strtoupper($attributeCode[0]);
        }

        $function = create_function('$c', 'return strtoupper($c[1]);');
        $functionName = preg_replace_callback('/_([a-z])/', $function, $attributeCode);

        return $prefix . $functionName;
    }
}
