<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use VitesseCms\Content\Models\Item;
use Phalcon\Events\Manager;

class InitiateListeners
{
    public static function setListeners(Manager $eventsManager): void
    {
        $eventsManager->attach(Item::class, new ModelItemListener());
        $eventsManager->attach('adminMenu', new AdminMenuListener());
    }
}
