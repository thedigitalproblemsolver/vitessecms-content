<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use VitesseCms\Communication\Models\NewsletterQueue;
use VitesseCms\Content\Helpers\EventVehicleHelper;
use VitesseCms\Content\Models\Item;
use VitesseCms\Database\Utils\MongoUtil;
use Phalcon\Di;

class TagSubscribeListener extends AbstractTagListener
{
    public function __construct()
    {
        $this->name = 'SUBSCRIBE';
    }

    protected function parse(EventVehicleHelper $eventVehicle, string $tagString): void
    {
        $tagOptions = explode(';', $tagString);
        $content = '';

        if(!empty($tagOptions[1])) :
            foreach (explode(',',$tagOptions[1]) as $itemId) :
                if(MongoUtil::isObjectId($itemId)) :
                    $item = Item::findById($itemId);
                    if($item) :
                        $email = 'jasper@craftbeershirts.net';
                        $target = '';
                        if($eventVehicle->_('newsletterQueueId')) :
                            /** @var NewsletterQueue $newsletterQueue */
                            $newsletterQueue = NewsletterQueue::findById($eventVehicle->_('newsletterQueueId'));
                            if($newsletterQueue) :
                                $email = $newsletterQueue->_('email');
                                $target = 'target="_blank"';
                            endif;
                        elseif(Di::getDefault()->get('user')->_('email')) :
                            $email = Di::getDefault()->get('user')->_('email');
                        endif;
                        $subscribeLink = $eventVehicle->getUrl()->getBaseUri().$item->_('slug').'?e='.base64_encode($email).'&';
                        $content = str_replace(
                            ['{SUBSCRIBE'.$tagString.'}','{/SUBSCRIBE}'],
                            ['<a href="'.$subscribeLink.'" class="link-subscribe" style="text-decoration:none;color:#ffffff" '.$target.' >','</a>'],
                            $eventVehicle->_('content')
                        );
                    endif;
                endif;
            endforeach;
        endif;

        $eventVehicle->set('content', $content);
    }
}
