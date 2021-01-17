<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use VitesseCms\Content\Helpers\EventVehicleHelper;
use VitesseCms\Shop\Models\Country;
use VitesseCms\Shop\Models\Order;

/**
 * Class TagUnsubscribeListener
 */
class TagOrderSendDateListener extends AbstractTagListener
{
    /**
     * TagShopTrackAndTraceListener constructor.
     */
    public function __construct()
    {
        $this->name = 'ORDER_SENDDATE';
    }

    /**
     * @inheritdoc
     */
    protected function parse(EventVehicleHelper $eventVehicle, string $tagString): void
    {
        $replace = '';
        if (!empty($eventVehicle->_('orderId'))) :
            /** @var Order $order */
            $order = Order::findById($eventVehicle->_('orderId'));
            $shipToCountry = Country::findById($order->_('shiptoAddress')['country']);
            $deliveryDays = 12;
            foreach ((array)$order->_('items') as $orderItem) :
                if (
                    isset($orderItem[0]['maximumDeliveryTime'][strtolower($shipToCountry->getShort())])
                    && $deliveryDays < $orderItem[0]['maximumDeliveryTime'][strtolower($shipToCountry->getShort())]
                ) {
                    $deliveryDays = $orderItem[0]['maximumDeliveryTime'][strtolower($shipToCountry->getShort())];
                }
            endforeach;
            $interval = $order->getCreateDate()->diff(new \DateTime());
            $deliveryDays -= (int)$interval->format('%a');
            if($deliveryDays < 3 ) {
                $deliveryDays = 3;
            }
            $replace = (new \DateTime())->modify('+'.$deliveryDays.' days')->format('d-m-Y');
        endif;

        $content = str_replace('{ORDER_SENDDATE}', $replace, $eventVehicle->_('content'));
        $eventVehicle->set('content', $content);
    }
}
