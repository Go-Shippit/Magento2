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

namespace Shippit\Shipping\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Shippit\Shipping\Helper\Data;

use \Magento\Framework\ObjectManagerInterface;


class SyncOrdersCommand extends Command
{
    protected $_objectManager;
    protected $_helper;
    protected $_api;

    public function __construct(
        ObjectManagerInterface $manager,
        \Magento\Framework\App\State $state
    ) {
        $this->_objectManager = $manager;
        $state->setAreaCode('adminhtml');

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('shippit:sync-orders')->setDescription('Sync Orders with the Shippit Platform');
    }
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->_objectManager;

        $api = $manager->create('\Shippit\Shipping\Model\Api\Order');
        $api->run();
    }
}