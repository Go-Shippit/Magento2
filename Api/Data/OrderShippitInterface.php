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

use Shippit\Shipping\Api\Data\OrderShippitOrderShippitReturnAddressInterface;

interface OrderShippitInterface
{
    const RETURN_ADDRESS = 'return_address';
    const RETURN_MERCHANT_ID = 'return_merchant_id';

    /**
     * Get the return address
     *
     * @return \Shippit\Shipping\Api\Data\OrderShippitReturnAddressInterface
     */
    public function getReturnAddress();

    /**
     * Set the return address
     *
     * @param \Shippit\Shipping\Api\Data\OrderShippitReturnAddressInterface $returnAddress
     * @return \Shippit\Shipping\Api\Data\OrderShippitReturnAddressInterface
     */
    public function setReturnAddress(OrderShippitReturnAddressInterface $returnAddress);

    /**
     * Get the return merchant id
     *
     * @return int
     */
    public function getReturnMerchantId();

    /**
     * Set the return store
     *
     * @param int $returnMerchantId
     * @return int
     */
    public function setReturnMerchantId($returnMerchantId);
}
