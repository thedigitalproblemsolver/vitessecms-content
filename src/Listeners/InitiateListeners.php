<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use Phalcon\Events\Manager;
use VitesseCms\Content\Blocks\MainContent;
use VitesseCms\Content\Controllers\AdminitemController;
use VitesseCms\Content\Listeners\Admin\AdminMenuListener;
use VitesseCms\Content\Listeners\Blocks\BlockMainContentListener;
use VitesseCms\Content\Listeners\Controllers\AdminItemControllerListener;
use VitesseCms\Content\Listeners\Tags\TagDiscountListener;
use VitesseCms\Content\Listeners\Tags\TagItemListener;
use VitesseCms\Content\Listeners\Tags\TagOrderSendDateListener;
use VitesseCms\Content\Listeners\Tags\TagShopTrackAndTraceListener;
use VitesseCms\Content\Listeners\Tags\TagSubscribeListener;
use VitesseCms\Content\Listeners\Tags\TagUnsubscribeListener;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;

class InitiateListeners
{
    public static function setListeners(Manager $eventsManager): void
    {
        $eventsManager->attach('adminMenu', new AdminMenuListener());
        $eventsManager->attach(AdminitemController::class, new AdminItemControllerListener());
        $eventsManager->attach(MainContent::class, new BlockMainContentListener(
            new DatagroupRepository()
        ));
        $eventsManager->attach('contentTag', new TagDiscountListener());
        $eventsManager->attach('contentTag', new TagItemListener());
        $eventsManager->attach('contentTag', new TagUnsubscribeListener());
        $eventsManager->attach('contentTag', new TagShopTrackAndTraceListener());
        $eventsManager->attach('contentTag', new TagSubscribeListener());
        $eventsManager->attach('contentTag', new TagOrderSendDateListener());
    }
}
