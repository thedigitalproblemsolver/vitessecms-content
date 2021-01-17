<?php declare(strict_types=1);

namespace VitesseCms\Content\Forms;

use VitesseCms\Content\Repositories\AdminRepositoryCollection;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;

class NewItemForm extends AbstractForm
{
    /**
     * @var AdminRepositoryCollection
     */
    protected $repositories;

    /**
     * @var ?string
     */
    protected $parentId;

    public function build(): NewItemForm
    {
        if ($this->parentId !== null) :
            $parent = $this->repositories->item->getById($this->parentId, false);
            $datagroups = $this->repositories->datagroup->findAllByParentId($parent->getDatagroup());
        else :
            $datagroups = $this->repositories->datagroup->findAll(null,false);
        endif;

        $this->addDropdown(
            '%ADMIN_TYPE%',
            'datagroup',
            (new Attributes())
                ->setRequired(true)
                ->setOptions(ElementHelper::modelIteratorToOptions($datagroups))
        )
            ->addHidden('parentId', $this->parentId)
            ->addAcl('%ADMIN_PERMISSION_ROLES%', 'roles')
            ->addSubmitButton('%CORE_SAVE%');

        return $this;
    }

    public function setParentId(?string $parentId): NewItemForm
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function setRepositories(AdminRepositoryCollection $repositories): NewItemForm
    {
        $this->repositories = $repositories;

        return $this;
    }
}
