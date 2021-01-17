<?php declare(strict_types=1);

namespace VitesseCms\Content\Forms;

use VitesseCms\Content\Repositories\AdminRepositoryCollection;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Content\Models\Item;

class ItemForm extends AbstractForm
{
    /**
     * @var AdminRepositoryCollection
     */
    protected $repositories;

    public function build(Item $item): ItemForm
    {
        $datagroup = $this->repositories->datagroup->getById($item->getDatagroup(), false);
        if ($datagroup !== null) {
            $datagroup->buildItemForm($this, $item);

            $this->addNumber('%ADMIN_ORDERING%', 'ordering')
                ->addAcl('%ADMIN_PERMISSION_ROLES%', 'roles')
                ->addSubmitButton('%CORE_SAVE%');
        }

        return $this;
    }

    public function setRepositories(AdminRepositoryCollection $repositories): ItemForm
    {
        $this->repositories = $repositories;

        return $this;
    }
}
