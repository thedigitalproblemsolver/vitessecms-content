<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use VitesseCms\Content\Blocks\Filter;
use VitesseCms\Content\Blocks\FilterResult;
use VitesseCms\Content\Blocks\Itemlist;
use VitesseCms\Content\Blocks\MainContent;
use VitesseCms\Content\Blocks\Texteditor;
use VitesseCms\Content\Controllers\AdminitemController;
use VitesseCms\Content\Fields\Model;
use VitesseCms\Content\Listeners\Admin\AdminMenuListener;
use VitesseCms\Content\Listeners\Blocks\BlockFilterListener;
use VitesseCms\Content\Listeners\Blocks\BlockFilterResultListener;
use VitesseCms\Content\Listeners\Blocks\BlockItemlistListener;
use VitesseCms\Content\Listeners\Blocks\BlockMainContentListener;
use VitesseCms\Content\Listeners\Blocks\BlockTexteditorListener;
use VitesseCms\Content\Listeners\Controllers\AdminItemControllerListener;
use VitesseCms\Content\Listeners\Fields\ModelListener;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\Interfaces\InitiateListenersInterface;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;

class InitiateAdminListeners implements InitiateListenersInterface
{
    public static function setListeners(InjectableInterface $di): void
    {
        $di->eventsManager->attach('adminMenu', new AdminMenuListener());
        $di->eventsManager->attach(AdminitemController::class, new AdminItemControllerListener());
        $di->eventsManager->attach(MainContent::class, new BlockMainContentListener(
            new DatagroupRepository()
        ));
        $di->eventsManager->attach(Filter::class, new BlockFilterListener(
            new DatagroupRepository(),
            new ItemRepository()
        ));
        $di->eventsManager->attach(FilterResult::class, new BlockFilterResultListener());
        $di->eventsManager->attach(Texteditor::class, new BlockTexteditorListener());
        $di->eventsManager->attach(Itemlist::class, new BlockItemlistListener(
            new ItemRepository()
        ));
        $di->eventsManager->attach(Model::class, new ModelListener());
    }
}
