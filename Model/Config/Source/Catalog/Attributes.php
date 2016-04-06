<?php
/**
*  Shippit Pty Ltd
*
*  NOTICE OF LICENSE
*
*  This source file is subject to the terms
*  that is available through the world-wide-web at this URL:
*  http://www.shippit.com/terms
*
*  @category   Shippit
*  @copyright  Copyright (c) 2016 by Shippit Pty Ltd (http://www.shippit.com)
*  @author     Matthew Muscat <matthew@mamis.com.au>
*  @license    http://www.shippit.com/terms
*/

class Shippit_Shippit_Model_System_Config_Source_Catalog_Attributes
{
    /**
     * Returns code => code pairs of attributes for all product attributes
     *
     * @return array
     */
    public function toOptionArray()
    {
        $entityType = Mage::getModel('eav/entity_type')
            ->loadByCode(Mage_Catalog_Model_Product::ENTITY);

        $attributes = Mage::getModel('eav/entity_attribute')
            ->getCollection()
            ->addFieldToSelect('attribute_code')
            ->setEntityTypeFilter($entityType)
            ->setOrder('attribute_code', 'ASC');

        $attributeArray[] = array(
            'label' => ' -- Please Select -- ',
            'value' => ''
        );

        foreach ($attributes as $attribute)
        {
            $attributeArray[] = array(
                'label' => $attribute->getAttributeCode(),
                'value' => $attribute->getAttributeCode()
            );
        }
        
        return $attributeArray;
    }
}