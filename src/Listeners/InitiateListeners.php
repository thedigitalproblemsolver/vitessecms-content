<?php

declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use VitesseCms\Content\Blocks\MainContent;
use VitesseCms\Content\Controllers\AdminitemController;
use VitesseCms\Content\Enum\ContentEnum;
use VitesseCms\Content\Enum\ItemEnum;
use VitesseCms\Content\Listeners\Admin\AdminMenuListener;
use VitesseCms\Content\Listeners\Blocks\BlockMainContentListener;
use VitesseCms\Content\Listeners\ContentTags\TagItemListener;
use VitesseCms\Content\Listeners\Controllers\AdminItemControllerListener;
use VitesseCms\Content\Listeners\Services\ContentServiceListener;
use VitesseCms\Content\Repositories\AdminRepositoryCollection;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\Enum\FrontendEnum;
use VitesseCms\Core\Interfaces\InitiateListenersInterface;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Language\Models\Language;
use VitesseCms\Language\Repositories\LanguageRepository;

final class InitiateListeners implements InitiateListenersInterface
{
    public static function setListeners(InjectableInterface $di): void
    {
        if ($di->user->hasAdminAccess()):
            $di->eventsManager->attach('adminMenu', new AdminMenuListener());
        endif;
        $di->eventsManager->attach(
            AdminitemController::class,
            new AdminItemControllerListener(
                new AdminRepositoryCollection(
                    new ItemRepository(),
                    new DatagroupRepository(),
                    new DatafieldRepository(),
                    new LanguageRepository(Language::class)
                ),
                new LanguageRepository(Language::class),
                $di->cache
            )
        );
        $di->eventsManager->attach(
            MainContent::class,
            new BlockMainContentListener(
                new DatagroupRepository(),
                $di->view->getCurrentItem()
            )
        );

        $di->eventsManager->attach('contentTag', new TagItemListener());
        $di->eventsManager->attach(
            ContentEnum::ATTACH_SERVICE_LISTENER,
            new ContentServiceListener(
                $di->view,
                $di->url,
                $di->eventsManager,
                $di->language,
                $di->setting
            )
        );
        $di->eventsManager->attach(ItemEnum::ITEM_LISTENER, new ItemListener(new ItemRepository()));
        $di->eventsManager->attach(
            FrontendEnum::LISTENER->value,
            new FrontendListener(
                $di->eventsManager,
                new DatagroupRepository(),
                new DatafieldRepository()
            )
        );
    }
}
