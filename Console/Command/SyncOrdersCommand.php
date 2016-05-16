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

namespace Shippit\Shipping\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncOrdersCommand extends Command
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_state;

    /**
     * Inject dependencies
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\State              $state
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\State $state
    ) {
        $this->_objectManager = $objectManager;
        $this->_state = $state;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('shippit:sync-orders')
            ->setDescription('Sync Orders with the Shippit Platform');

        parent::configure();
    }
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_state->setAreaCode('adminhtml');

        $manager = $this->_objectManager;

        $api = $manager->create('\Shippit\Shipping\Model\Api\Order');
        $api->run();
    }
}