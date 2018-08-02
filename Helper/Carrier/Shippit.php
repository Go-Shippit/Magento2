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

namespace Shippit\Shipping\Helper\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Shippit extends \Shippit\Shipping\Helper\Data
{
    const XML_PATH_SETTINGS = 'carriers/shippit/';

    protected $_itemsHelper;
    protected $_productRepository;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Module\ModuleList $moduleList
     * @param \Shippit\Shipping\Helper\Sync\Order\Items $itemsHelper
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Module\ModuleList $moduleList,
        \Shippit\Shipping\Helper\Sync\Order\Items $itemsHelper,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->_itemsHelper = $itemsHelper;
        $this->_productRepository = $productRepository;

        parent::__construct(
            $scopeConfig,
            $moduleList
        );
    }

    /**
     * Return store config value for key
     *
     * @param   string $key
     * @return  string
     */
    public function getValue($key, $scope = 'website')
    {
        $path = self::XML_PATH_SETTINGS . $key;

        return $this->_scopeConfig->getValue($path, $scope);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return parent::isActive() && self::getValue('active');
    }

    public function getTitle()
    {
        return self::getValue('title');
    }

    public function getAllowedMethods()
    {
        return explode(',', self::getValue('allowed_methods'));
    }

    public function getMargin()
    {
        return self::getValue('margin');
    }

    public function getMarginAmount()
    {
        return self::getValue('margin_amount');
    }

    public function getMaxTimeslots()
    {
        return self::getValue('max_timeslots');
    }

    public function isEnabledProductActive()
    {
        return self::getValue('enabled_product_active');
    }

    public function getEnabledProductIds()
    {
        return explode(',', self::getValue('enabled_product_ids'));
    }

    public function isEnabledProductAttributeActive()
    {
        return self::getValue('enabled_product_attribute_active');
    }

    public function getEnabledProductAttributeCode()
    {
        return self::getValue('enabled_product_attribute_code');
    }

    public function getEnabledProductAttributeValue()
    {
        return self::getValue('enabled_product_attribute_value');
    }

    public function getProductById($id)
    {
        return $this->_productRepository->getById($id);
    }

    public function getWidth($item)
    {
        $attributeCode = $this->_itemsHelper->getProductDimensionWidthAttributeCode();

        if (empty($attributeCode)) {
            return;
        }

        $product = $this->getProductById($item->getProductId());

        $attributeValue = $this->_itemsHelper->getAttributeValue($product, $attributeCode);

        return $this->_itemsHelper->getDimension($attributeValue);
    }

    public function getLength($item)
    {
        $attributeCode = $this->_itemsHelper->getProductDimensionLengthAttributeCode();

        if (empty($attributeCode)) {
            return;
        }

        $product = $this->getProductById($item->getProductId());

        $attributeValue = $this->_itemsHelper->getAttributeValue($product, $attributeCode);

        return $this->_itemsHelper->getDimension($attributeValue);
    }

    public function getDepth($item)
    {
        $attributeCode = $this->_itemsHelper->getProductDimensionDepthAttributeCode();

        if (empty($attributeCode)) {
            return;
        }

        $product = $this->getProductById($item->getProductId());

        $attributeValue = $this->_itemsHelper->getAttributeValue($product, $attributeCode);

        return $this->_itemsHelper->getDimension($attributeValue);
    }
}
