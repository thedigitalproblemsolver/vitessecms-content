<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use Phalcon\Events\Event;
use VitesseCms\Admin\Models\AdminMenu;
use VitesseCms\Admin\Models\AdminMenuNavBarChildren;
use VitesseCms\Datagroup\Models\Datagroup;

class AdminMenuListener
{
    public function AddChildren(Event $event, AdminMenu $adminMenu): void
    {
        if ($adminMenu->getUser()->getPermissionRole() === 'superadmin') :
            $adminMenu = $this->addContentGroup('content', 'Content', $adminMenu);
            $adminMenu = $this->addContentGroup('formOptions', 'DataDesign', $adminMenu);
        endif;
    }

    protected function addContentGroup(string $group, string $menuItem, AdminMenu $adminMenu): AdminMenu
    {
        $formOptionsGroups = $adminMenu->getGroups()->getByKey($group);
        if ($formOptionsGroups !== null) :
            $children = new AdminMenuNavBarChildren();
            $datagroups = $formOptionsGroups->getDatagroups();
            while ($datagroups->valid()) :
                $formOptionGroup = $datagroups->current();
                $children->addChild(
                    $formOptionGroup->getNameField(),
                    'admin/content/adminitem/adminList/?filter[datagroup]=' . $formOptionGroup->getId()
                );
                $datagroups->next();
            endwhile;
            $adminMenu->addDropdown($menuItem, $children);
        endif;

        return $adminMenu;
    }
}
