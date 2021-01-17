<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use VitesseCms\Admin\Models\AdminMenu;
use VitesseCms\Admin\Models\AdminMenuNavBarChildren;
use VitesseCms\Core\Models\Datagroup;
use Phalcon\Events\Event;

class AdminMenuListener
{
    public function AddChildren(Event $event, AdminMenu $adminMenu): void
    {
        if ($adminMenu->getUser()->getPermissionRole() === 'superadmin') :
            $group = $adminMenu->getGroups()->getByKey('content');
            if ($group !== null) :
                $children = new AdminMenuNavBarChildren();

                /** @var Datagroup $contentGroup */
                foreach ($group->getDatagroups() as $contentGroup) :
                    $children->addChild(
                        $contentGroup->getNameField(),
                        'admin/content/adminitem/adminList/?filter[datagroup]='.(string)$contentGroup->getId()
                    );
                endforeach;

                $adminMenu->addDropbown('Content', $children);
            endif;
        endif;
    }
}
