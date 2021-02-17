<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use VitesseCms\Content\Controllers\AdminitemController;
use VitesseCms\Content\Listeners\AdminItemControllerListener;
use VitesseCms\Content\Models\Item;
use Phalcon\Events\Manager;

class InitiateAdminListeners
{
    public static function setListeners(Manager $eventsManager): void
    {
        $eventsManager->attach('adminMenu', new AdminMenuListener());
        $eventsManager->attach(Item::class, new ModelItemListener());
        $eventsManager->attach(AdminitemController::class, new AdminItemControllerListener());
    }
}
