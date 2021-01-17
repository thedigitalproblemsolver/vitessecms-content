<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use VitesseCms\Content\Helpers\EventVehicleHelper;

class TagUnsubscribeListener extends AbstractTagListener
{
    public function __construct()
    {
        $this->name = 'UNSUBSCRIBE';
    }

    protected function parse(EventVehicleHelper $eventVehicle, string $tagString): void
    {
        $unsubscribeLink = $eventVehicle->getUrl()->getBaseUri().
            'communication/newsletterqueue/unsubscribe/'.
            $eventVehicle->_('newsletterQueueId')
        ;

        $content = str_replace(
            ['{UNSUBSCRIBE}','{/UNSUBSCRIBE}'],
            ['<a href="'.$unsubscribeLink.'" class="link-unsubscribe" style="text-decoration:none" target="_blank" >','</a>'],
            $eventVehicle->_('content')
        );
        $eventVehicle->set('content', $content);
    }
}
