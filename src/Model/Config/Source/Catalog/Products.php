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

class Products implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface
     */
    protected $product;

    /**
     * Inject Dependancies
     */
    public function __construct(
        \Magento\Catalog\Api\Data\ProductInterface $product
    ) {
        $this->product = $product;
    }

    /**
     * Returns code => code pairs of attributes for all product attributes
     *
     * @return array
     */
    public function toOptionArray()
    {
        $products = $this->product
            ->getCollection()
            ->addAttributeToSelect('name')
            ->setOrder('name', 'ASC');

        $productArray = [];

        foreach ($products as $product) {
            $productArray[] = [
                'label' => $product->getName(),
                'value' => $product->getId(),
            ];
        }

        return $productArray;
    }
}
