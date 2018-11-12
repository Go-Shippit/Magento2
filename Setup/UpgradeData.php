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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var ScopeConfigInterface
     */
    protected $configScope;

    /**
     * @var CollectionFactory
     */
    protected $configCollectionFactory;

    public function __construct(
        ConfigInterface $config,
        ScopeConfigInterface $configScope,
        CollectionFactory $configCollectionFactory
    )
    {
        $this->config = $config;
        $this->configScope = $configScope;
        $this->configCollectionFactory = $configCollectionFactory;
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
            $this->upgrade_1119($installer);
        }

        if (version_compare($context->getVersion(), '1.2.6') < 0) {
            $this->upgrade_126($installer);
        }

        if (version_compare($context->getVersion(), '1.4.10') < 0) {
            $this->upgrade_1410($installer);
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
            $configOptionValue = $this->configScope->getValue($configOptionOldKey);

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

    /**
     * Update config data to v1.2.6
     */
    public function upgrade_126($installer)
    {
        /**
         * Migrate settings data to v1.2.6
         * (new product location configuration area)
         */
        $configOptions = [
            'shippit/sync_order/product_location_attribute_code' => 'shippit/sync_item/product_location_active',
            'shippit/sync_order/product_location_attribute_code' => 'shippit/sync_item/product_location_attribute_code'
        ];

        foreach ($configOptions as $configOptionOldKey => $configOptionNewKey) {
            $configOptionValue = $this->configScope->getValue($configOptionOldKey);

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

    /**
     * Update config data to v1.4.10
     */
    public function upgrade_1410($installer)
    {
        /**
         * Migrate settings data to v1.4.10
         * (new shipping method carrier path for click and collect)
         */
        $configOptions = $this->configCollectionFactory
            ->create()
            ->addFieldToFilter('path', array('like' => 'carriers/shippit_cc/%'))
            ->load();

        if ($configOptions->getSize() <= 0) {
            return;
        }

        foreach ($configOptions->getItems() as $configOption) {
            $newConfigPath = str_replace(
                'shippit_cc',
                'shippitcc',
                $configOption->getPath()
            );

            $this->config->saveConfig(
                $newConfigPath,
                $configOption->getValue(),
                $configOption->getScope(),
                $configOption->getScopeId()
            );
        }
    }
}
