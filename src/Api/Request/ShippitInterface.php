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

namespace Shippit\Shipping\Api\Request;

interface ShippitInterface
{
    /**
     * Adds the order to the request queue, and if the mode is requested as realtime,
     * attempts to sync the record immediately.
     *
     * Note: Priority shipping services are only available via live quoting
     *
     * @param object|integer $order         The order entity_id
     * @param array   $items                An array of the items to be included
     * @param string  $shippingMethod       The shipping method service class to be used (standard, express)
     * @param string  $apiKey               The API Key to be used in the request
     * @param string  $syncMode             The sync mode ot be used for the request
     * @param boolean $displayNotifications Flag to indiciate if notifications should be shown to the user
     */
    public function addOrder(
        $order,
        $items = [],
        $shippingMethod = null,
        $apiKey = null,
        $syncMode = null,
        $displayNotifications = false
    );
}
