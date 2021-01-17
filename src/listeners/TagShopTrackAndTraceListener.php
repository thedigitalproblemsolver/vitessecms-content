<?php

namespace VitesseCms\Content\Listeners;

use VitesseCms\Content\Helpers\EventVehicleHelper;
use VitesseCms\Shop\Models\Order;
use VitesseCms\Shop\Models\Shipping;

/**
 * Class TagUnsubscribeListener
 */

class TagShopTrackAndTraceListener extends AbstractTagListener
{
    /**
     * TagShopTrackAndTraceListener constructor.
     */
    public function __construct()
    {
        $this->name = 'TRACKANDTRACE';
    }

    /**
     * @inheritdoc
     */
    protected function parse(EventVehicleHelper $eventVehicle, string $tagString): void
    {
        if(!empty($eventVehicle->_('orderId'))) :
            $order = Order::findById($eventVehicle->_('orderId'));
            $shipping = Shipping::findById((string)$order->_('shippingType')['_id']);

            $link = $shipping->getTrackAndTraceLink($order);
            $replace = '';
            if(!empty($link)) :
                $replace = ['<a href="'.$link.'" class="link-trackandtrace" style="text-decoration:none" target="_blank" >','</a>'];
            endif;
            $content = str_replace(['{TRACKANDTRACE}','{/TRACKANDTRACE}'], $replace, $eventVehicle->_('content'));
            $eventVehicle->set('content', $content);
        endif;
    }
}
