<?php

namespace Shippit\Shipping\Model\Config\Source\Shipping;

use Magento\Store\Model\ScopeInterface;

class Methods implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $_shippingConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Shipping\Model\Config $shippingConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Config $shippingConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_shippingConfig = $shippingConfig;
    }

    /**
     * Return array of carriers.
     *
     * @param bool $isActiveOnlyFlag
     * @return array
     */
    public function toOptionArray($showPlaceholder = false, $excludeShippit = false)
    {
        if (!$showPlaceholder) {
            $methods = [[
                'value' => '',
                'label' => '-- Please Select --'
            ]];
        }

        $carriers = $this->_shippingConfig->getAllCarriers();

        foreach ($carriers as $carrierCode => $carrierModel) {
            $carrierMethods = $carrierModel->getAllowedMethods();

            // if the carrier is shippit, exclude
            // it from the returned results
            if ($excludeShippit && ($carrierCode == 'shippit' || $carrierCode == 'shippit_cc')
                ) {
                continue;
            }

            if ($carrierMethods) {
                $carrierTitle = $this->_scopeConfig->getValue(
                    'carriers/' . $carrierCode . '/title',
                    ScopeInterface::SCOPE_STORE
                );

                $methods[$carrierCode] = [
                    'label' => $carrierTitle,
                    'value' => [],
                ];

                foreach ($carrierMethods as $methodCode => $methodTitle) {
                    $methods[$carrierCode]['value'][] = [
                        'value' => $carrierCode . '_' . $methodCode,
                        'label' => '[' . $carrierCode . '] ' . $methodTitle,
                    ];
                }
            }
        }

        return $methods;
    }
}
