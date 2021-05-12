<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use Phalcon\Events\Manager;
use VitesseCms\Content\Blocks\MainContent;
use VitesseCms\Content\Controllers\AdminitemController;

class InitiateListeners
{
    public static function setListeners(Manager $eventsManager): void
    {
        $eventsManager->attach('adminMenu', new AdminMenuListener());
        $eventsManager->attach(AdminitemController::class, new AdminItemControllerListener());
        $eventsManager->attach(MainContent::class, new BlockMainContentListener());
    }
}
