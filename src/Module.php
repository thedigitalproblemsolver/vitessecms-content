<?php declare(strict_types=1);

namespace VitesseCms\Content;

use VitesseCms\Admin\Utils\AdminUtil;
use VitesseCms\Content\Repositories\AdminRepositoryCollection;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Content\Repositories\RepositoryCollection;
use VitesseCms\Core\AbstractModule;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use Phalcon\Di\DiInterface;
use VitesseCms\Language\Repositories\LanguageRepository;

class Module extends AbstractModule
{
    public function registerServices(DiInterface $di, string $string = null)
    {
        parent::registerServices($di, 'Content');

        if (AdminUtil::isAdminPage()) :
            $di->setShared('repositories', new AdminRepositoryCollection(
                new ItemRepository(),
                new DatagroupRepository(),
                new DatafieldRepository(),
                new LanguageRepository()
            ));
        else :
            $di->setShared('repositories', new RepositoryCollection(
                new ItemRepository(),
                new DatagroupRepository()
            ));
        endif;
    }
}
