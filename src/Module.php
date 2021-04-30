<?php declare(strict_types=1);

namespace VitesseCms\Content;

use VitesseCms\Admin\Utils\AdminUtil;
use VitesseCms\Communication\Services\MailchimpService;
use VitesseCms\Content\Repositories\AdminRepositoryCollection;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Content\Repositories\RepositoryCollection;
use VitesseCms\Core\AbstractModule;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use Phalcon\DiInterface;
use VitesseCms\Language\Repositories\LanguageRepository;

class Module extends AbstractModule
{
    public function registerServices(DiInterface $di, string $string = null)
    {
        parent::registerServices($di, 'Content');

        $di->setShared('mailchimp', new MailchimpService(
            $di->get('session'),
            $di->get('setting'),
            $di->get('url'),
            $di->get('configuration')
        ));

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
