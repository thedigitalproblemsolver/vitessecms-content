<?php declare(strict_types=1);

namespace VitesseCms\Content;

use VitesseCms\Admin\Utils\AdminUtil;
use VitesseCms\Communication\Services\MailchimpService;
use VitesseCms\Content\Repositories\AdminRepositoryCollection;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\AbstractModule;
use VitesseCms\Core\Repositories\DatagroupRepository;
use Phalcon\DiInterface;

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
                new DatagroupRepository()
            ));
        endif;
    }
}
