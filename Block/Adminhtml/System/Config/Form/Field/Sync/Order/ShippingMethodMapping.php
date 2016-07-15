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

namespace Shippit\Shipping\Block\Adminhtml\System\Config\Form\Field\Sync\Order;

class ShippingMethodMapping extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    protected $_columns = [];
    protected $_shippingMethodRenderer;
    protected $_shippitServiceClassRenderer;

    /**
     * Enable the "Add after" button or not
     *
     * @var bool
     */
    protected $_addAfter = true;

    /**
     * Label of add button
     *
     * @var string
     */
    protected $_addButtonLabel;

    /**
     * Returns renderer for Shippit Service Classes
     *
     * @return \Shippit\Shipping\Block\Adminhtml\Config\Form\Field\Shipping\Method
     */
    protected function getShippingMethodRenderer()
    {
        if (!$this->_shippingMethodRenderer) {
            $this->_shippingMethodRenderer = $this->getLayout()
                ->createBlock(
                    '\Shippit\Shipping\Block\Adminhtml\System\Config\Form\Field\Shipping\Methods',
                    '',
                    ['data' => ['is_render_to_js_template' => true]]
                );
        }

        return $this->_shippingMethodRenderer;
    }

    /**
     * Returns renderer for Shippit Service Classes
     *
     * @return \Shippit\Shipping\Block\Adminhtml\Config\Form\Field\Shippit\ServiceClass
     */
    protected function getShippitServiceClassRenderer()
    {
        if (!$this->_shippitServiceClassRenderer) {
            $this->_shippitServiceClassRenderer = $this->getLayout()
                ->createBlock(
                    '\Shippit\Shipping\Block\Adminhtml\System\Config\Form\Field\Shippit\ServiceClasses',
                    '',
                    ['data' => ['is_render_to_js_template' => true]]
                );
        }

        return $this->_shippitServiceClassRenderer;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'shipping_method',
            [
                'label' => __('Shipping Method'),
                'renderer' => $this->getShippingMethodRenderer()
            ]
        );

        $this->addColumn(
            'shippit_service_class',
            [
                'label' => __('Shippit Service Class'),
                'renderer' => $this->getShippitServiceClassRenderer()
            ]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Mapping');
    }

    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options = [];
        $shippingMethod = $row->getShippingMethod();
        $shippitServiceClass = $row->getShippitServiceClass();
        
        if ($shippingMethod) {
            $options['option_' . $this->getShippingMethodRenderer()->calcOptionHash($shippingMethod)] = 'selected="selected"';
        }

        if ($shippitServiceClass) {
            $options['option_' . $this->getShippitServiceClassRenderer()->calcOptionHash($shippitServiceClass)] = 'selected="selected"';
        }
        
        $row->setData('option_extra_attrs', $options);
    }
}