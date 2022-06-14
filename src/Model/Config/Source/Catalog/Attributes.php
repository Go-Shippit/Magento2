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

namespace Shippit\Shipping\Model\Config\Source\Catalog;

class Attributes implements \Magento\Framework\Option\ArrayInterface
{
    protected $_product;
    protected $_entityType;
    protected $_productAttributeCollection;

    /**
     * Inject Dependancies
     */
    public function __construct(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        \Magento\Eav\Model\Entity\Type $entityType,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $productAttributeCollection
    ) {
        $this->_product = $product;
        $this->_entityType = $entityType;
        $this->_productAttributeCollection = $productAttributeCollection;
    }

    /**
     * Returns code => code pairs of attributes for all product attributes
     *
     * @return array
     */
    public function toOptionArray()
    {
        $entityType = $this->_entityType
            ->loadByCode($this->_product->getEntityId());

        $attributes = $this->_productAttributeCollection->create();

        $attributes = $attributes->addFieldToSelect('attribute_code')
            ->setEntityTypeFilter($entityType)
            ->setOrder('attribute_code', 'ASC')
            ->getItems();

        $attributeArray[] = [
            'label' => ' -- Please Select -- ',
            'value' => ''
        ];

        foreach ($attributes as $attribute) {
            $attributeArray[] = [
                'label' => $attribute->getAttributeCode(),
                'value' => $attribute->getAttributeCode()
            ];
        }

        return $attributeArray;
    }
}
