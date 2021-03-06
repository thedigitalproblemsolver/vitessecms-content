<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use VitesseCms\Content\Blocks\MainContent;
use VitesseCms\Content\Controllers\AdminitemController;
use VitesseCms\Content\Listeners\Admin\AdminMenuListener;
use VitesseCms\Content\Listeners\Blocks\BlockMainContentListener;
use VitesseCms\Content\Listeners\Controllers\AdminItemControllerListener;
use VitesseCms\Content\Listeners\ContentTags\TagItemListener;
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

        $di->eventsManager->attach('contentTag', new TagItemListener());
    }
}
