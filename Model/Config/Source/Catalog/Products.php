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

class Shippit_Shippit_Model_System_Config_Source_Catalog_Products
{
    /**
     * Returns code => code pairs of attributes for all product attributes
     *
     * @return array
     */
    public function toOptionArray()
    {
        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('name')
            ->setOrder('name', 'ASC');
        
        foreach ($products as $product)
        {
            $productArray[] = array(
                'label' => $product->getName(),
                'value' => $product->getId()
            );
        }
        
        return $productArray;
    }
}