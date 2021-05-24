<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners\Tags;

use VitesseCms\Content\Helpers\EventVehicleHelper;
use VitesseCms\Shop\Models\Order;
use VitesseCms\Shop\Models\Shipping;

/**
 * @deprecated move to shop
 */
class TagShopTrackAndTraceListener extends AbstractTagListener
{
    public function __construct()
    {
        $this->name = 'TRACKANDTRACE';
    }

    protected function parse(EventVehicleHelper $eventVehicle, string $tagString): void
    {
        if (!empty($eventVehicle->_('orderId'))) :
            $order = Order::findById($eventVehicle->_('orderId'));
            $shipping = Shipping::findById((string)$order->_('shippingType')['_id']);

            $link = $shipping->getTrackAndTraceLink($order);
            $replace = '';
            if (!empty($link)) :
                $replace = ['<a href="' . $link . '" class="link-trackandtrace" style="text-decoration:none" target="_blank" >', '</a>'];
            endif;
            $content = str_replace(['{TRACKANDTRACE}', '{/TRACKANDTRACE}'], $replace, $eventVehicle->_('content'));
            $eventVehicle->set('content', $content);
        endif;
    }
}
