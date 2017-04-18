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

namespace Shippit\Shipping\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $config,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Upgrade DB Data for the Shippit Shipping Module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.1.19') < 0) {
            //code to upgrade to 1.1.19
            $this->upgrade_1119($installer);
        }

        $installer->endSetup();
    }

    /**
     * Update config data to v1.1.19
     */
    public function upgrade_1119($installer)
    {
        /**
         * Migrate settings data from v1.1.18 to v1.1.19
         */
        $configOptions = [
            'carriers/shippit/product_attribute_code' => 'carriers/shippit/enabled_product_attribute_code',
            'carriers/shippit/product_attribute_value' => 'carriers/shippit/enabled_product_attribute_value'
        ];

        foreach ($configOptions as $configOptionOldKey => $configOptionNewKey) {
            $configOptionValue = $this->scopeConfig->getValue($configOptionOldKey);

            if (!empty($configOptionValue)) {
                $this->config->saveConfig(
                    $configOptionNewKey,
                    $configOptionValue,
                    'default',
                    0
                );
            }
        }
    }
}
