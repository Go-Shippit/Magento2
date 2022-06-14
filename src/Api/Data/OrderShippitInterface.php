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
 * @copyright  Copyright (c) 2017 by Shippit Pty Ltd (http://www.shippit.com)
 * @author     Matthew Muscat <matthew@mamis.com.au>
 * @license    http://www.shippit.com/terms
 */

namespace Shippit\Shipping\Api\Data;

use Shippit\Shipping\Api\Data\OrderShippitMerchantInterface;

interface OrderShippitInterface
{
    const MERCHANTS = 'merchants';

    /**
     * Get the merchants for the order
     *
     * @return \Shippit\Shipping\Api\Data\OrderShippitMerchantInterface[]
     */
    public function getMerchants();

    /**
     * Set the merchants for the order
     *
     * @param \Shippit\Shipping\Api\Data\OrderShippitMerchantInterface[] $merchants
     * @return $this
     */
    public function setMerchants(array $merchants);

    /**
     * Add a merchants to the order
     *
     * @param \Shippit\Shipping\Api\Data\OrderShippitMerchantInterface $merchant
     * @return $this
     */
    public function addMerchant(OrderShippitMerchantInterface $merchant);
}
