<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use VitesseCms\Block\Models\BlockMainContent;
use VitesseCms\Content\Controllers\AdminitemController;
use Phalcon\Events\Manager;

class InitiateAdminListeners
{
    public static function setListeners(Manager $eventsManager): void
    {
        $eventsManager->attach('adminMenu', new AdminMenuListener());
        $eventsManager->attach(AdminitemController::class, new AdminItemControllerListener());
        $eventsManager->attach(BlockMainContent::class, new BlockMainContentListener());
    }
}
