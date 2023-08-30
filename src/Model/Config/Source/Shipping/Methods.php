<?php

namespace Shippit\Shipping\Model\Config\Source\Shipping;

use Shippit\Shipping\Helper\Data as ShippitHelper;
use Magento\Store\Model\ScopeInterface;

class Methods implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Shipping\Model\Config $shippingConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Config $shippingConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->shippingConfig = $shippingConfig;
    }

    /**
     * Return array of carriers.
     *
     * @param boolean $showPlaceholder
     * @param boolean $excludeShippit
     * @return array
     */
    public function toOptionArray($showPlaceholder = false, $excludeShippit = false)
    {
        $methods = [];

        if (!$showPlaceholder) {
            $methods[] = [
                'value' => '',
                'label' => '-- Please Select --',
            ];
        }

        $carriers = $this->shippingConfig->getAllCarriers();

        foreach ($carriers as $carrierCode => $carrierModel) {
            $carrierMethods = $carrierModel->getAllowedMethods();

            // if the carrier is shippit, exclude
            // it from the returned results
            if ($excludeShippit &&
                (
                    $carrierCode == ShippitHelper::CARRIER_CODE ||
                    $carrierCode == ShippitHelper::CARRIER_CODE_CC ||
                    $carrierCode == ShippitHelper::CARRIER_CODE_CC_LEGACY
                )
            ) {
                continue;
            }

            if ($carrierMethods) {
                $carrierTitle = $this->scopeConfig->getValue(
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
