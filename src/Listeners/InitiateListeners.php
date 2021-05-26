<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

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
use VitesseCms\Core\Interfaces\InitiateListenersInterface;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;

class InitiateListeners implements InitiateListenersInterface
{
    public static function setListeners(InjectableInterface $di): void
    {
        if($di->user->hasAdminAccess()):
            $di->eventsManager->attach('adminMenu', new AdminMenuListener());
        endif;
        $di->eventsManager->attach(AdminitemController::class, new AdminItemControllerListener());
        $di->eventsManager->attach(MainContent::class, new BlockMainContentListener(
            new DatagroupRepository()
        ));
        $di->eventsManager->attach('contentTag', new TagDiscountListener());
        $di->eventsManager->attach('contentTag', new TagItemListener());
        $di->eventsManager->attach('contentTag', new TagUnsubscribeListener());
        $di->eventsManager->attach('contentTag', new TagShopTrackAndTraceListener());
        $di->eventsManager->attach('contentTag', new TagSubscribeListener());
        $di->eventsManager->attach('contentTag', new TagOrderSendDateListener());
    }
}
