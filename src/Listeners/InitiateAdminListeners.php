<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use VitesseCms\Block\Models\Texteditor;
use VitesseCms\Content\Blocks\Filter;
use VitesseCms\Content\Blocks\FilterResult;
use VitesseCms\Content\Blocks\MainContent;
use VitesseCms\Content\Controllers\AdminitemController;
use Phalcon\Events\Manager;

class InitiateAdminListeners
{
    public static function setListeners(Manager $eventsManager): void
    {
        $eventsManager->attach('adminMenu', new AdminMenuListener());
        $eventsManager->attach(AdminitemController::class, new AdminItemControllerListener());
        $eventsManager->attach(MainContent::class, new BlockMainContentListener());
        $eventsManager->attach(Filter::class, new BlockFilterListener());
        $eventsManager->attach(FilterResult::class, new BlockFilterResultListener());
        $eventsManager->attach(Texteditor::class, new BlockTexteditorListener());
    }
}
