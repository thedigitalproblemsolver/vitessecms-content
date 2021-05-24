<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use VitesseCms\Content\Blocks\Filter;
use VitesseCms\Content\Blocks\FilterResult;
use VitesseCms\Content\Blocks\Itemlist;
use VitesseCms\Content\Blocks\MainContent;
use VitesseCms\Content\Blocks\Texteditor;
use VitesseCms\Content\Controllers\AdminitemController;
use Phalcon\Events\Manager;
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
use VitesseCms\Datagroup\Repositories\DatagroupRepository;

class InitiateAdminListeners
{
    public static function setListeners(Manager $eventsManager): void
    {
        $eventsManager->attach('adminMenu', new AdminMenuListener());
        $eventsManager->attach(AdminitemController::class, new AdminItemControllerListener());
        $eventsManager->attach(MainContent::class, new BlockMainContentListener(
            new DatagroupRepository()
        ));
        $eventsManager->attach(Filter::class, new BlockFilterListener(
            new DatagroupRepository(),
            new ItemRepository()
        ));
        $eventsManager->attach(FilterResult::class, new BlockFilterResultListener());
        $eventsManager->attach(Texteditor::class, new BlockTexteditorListener());
        $eventsManager->attach(Itemlist::class, new BlockItemlistListener(
            new ItemRepository()
        ));
        $eventsManager->attach(Model::class, new ModelListener());
    }
}
