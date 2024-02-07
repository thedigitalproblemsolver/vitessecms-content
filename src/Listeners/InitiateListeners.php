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
    public static function setListeners(InjectableInterface $injectable): void
    {
        if ($injectable->user->hasAdminAccess()):
            $injectable->eventsManager->attach('adminMenu', new AdminMenuListener());
        endif;
        $injectable->eventsManager->attach(
            AdminitemController::class,
            new AdminItemControllerListener(
                new AdminRepositoryCollection(
                    new ItemRepository(),
                    new DatagroupRepository(),
                    new DatafieldRepository(),
                    new LanguageRepository(Language::class)
                ),
                new LanguageRepository(Language::class),
                $injectable->cache
            )
        );
        $injectable->eventsManager->attach(
            MainContent::class,
            new BlockMainContentListener(
                new DatagroupRepository(),
                $injectable->view->getCurrentItem()
            )
        );

        $injectable->eventsManager->attach('contentTag', new TagItemListener());
        $injectable->eventsManager->attach(
            ContentEnum::ATTACH_SERVICE_LISTENER,
            new ContentServiceListener(
                $injectable->view,
                $injectable->url,
                $injectable->eventsManager,
                $injectable->language,
                $injectable->setting
            )
        );
        $injectable->eventsManager->attach(ItemEnum::ITEM_LISTENER, new ItemListener(new ItemRepository()));
        $injectable->eventsManager->attach(
            FrontendEnum::LISTENER->value,
            new FrontendListener(
                $injectable->eventsManager,
                new DatagroupRepository(),
                new DatafieldRepository()
            )
        );
    }
}
