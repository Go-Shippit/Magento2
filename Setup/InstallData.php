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

namespace Shippit\Shipping\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $tableCountryRegion = $setup->getTable('directory_country_region');
        $tableCountryRegionName = $setup->getTable('directory_country_region_name');

        $data = [
            ['AU', 'ACT', 'Australian Capital Territory'],
            ['AU', 'NSW', 'New South Wales'],
            ['AU', 'NT', 'Northern Territory'],
            ['AU', 'QLD', 'Queensland'],
            ['AU', 'SA', 'South Australia'],
            ['AU', 'TAS', 'Tasmania'],
            ['AU', 'VIC', 'Victoria'],
            ['AU', 'WA', 'Western Australia'],
        ];

        foreach ($data as $row) {
            $bind = [
                'country_id' => $row[0],
                'code' => $row[1],
                'default_name' => $row[2]
            ];
            $setup->getConnection()
                ->insert($tableCountryRegion, $bind);
            
            $regionId = $setup->getConnection()
                ->lastInsertId($setup->getTable('directory_country_region'));

            $columns = ['locale', 'region_id', 'name'];
            $values = [
                ['en_AU', $regionId, $row[2]],
                ['en_US', $regionId, $row[2]],
            ];

            $setup->getConnection()
                ->insertArray($tableCountryRegionName, $columns, $values);
        }
    }
}