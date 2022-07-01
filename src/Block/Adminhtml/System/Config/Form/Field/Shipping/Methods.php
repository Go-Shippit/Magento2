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

namespace Shippit\Shipping\Block\Adminhtml\System\Config\Form\Field\Shipping;

/**
 * Shipping Methods
 */
class Methods extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var Methods
     */
    protected $methods;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Methods $methods
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Shippit\Shipping\Model\Config\Source\Shipping\Methods $methods,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->methods = $methods;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->methods->toOptionArray(true, true));
        }

        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
