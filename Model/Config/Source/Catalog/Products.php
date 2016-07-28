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

namespace Shippit\Shipping\Model\Config\Source\Catalog;

class Products implements \Magento\Framework\Option\ArrayInterface
{
    protected $_product;

    /**
     * Inject Dependancies
     */
    public function __construct(
        \Magento\Catalog\Api\Data\ProductInterface $product
    ) {
        $this->_product = $product;
    }

    /**
     * Returns code => code pairs of attributes for all product attributes
     *
     * @return array
     */
    public function toOptionArray()
    {
        $products = $this->_product
            ->getCollection()
            ->addAttributeToSelect('name')
            ->setOrder('name', 'ASC');
    
        foreach ($products as $product) {
            $productArray[] = [
                'label' => $product->getName(),
                'value' => $product->getId()
            ];
        }
    
        return $productArray;
    }
}
