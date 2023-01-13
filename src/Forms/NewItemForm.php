<?php declare(strict_types=1);

namespace VitesseCms\Content\Forms;

use VitesseCms\Content\Repositories\AdminRepositoryCollection;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\AbstractFormWithRepository;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Interfaces\FormWithRepositoryInterface;
use VitesseCms\Form\Models\Attributes;

class NewItemForm extends AbstractFormWithRepository
{
    public function buildForm(): FormWithRepositoryInterface
    {
        $parentId = $this->request->get('parentId', null);
        if (!empty($parentId)) :
            $parent = $this->repositories->item->getById($parentId, false);
            $datagroups = $this->repositories->datagroup->findAllByParentId($parent->getDatagroup());
        else :
            $datagroups = $this->repositories->datagroup->findAll(null, false);
        endif;

        $this->addDropdown(
            '%ADMIN_TYPE%',
            'datagroup',
            (new Attributes())
                ->setRequired()
                ->setOptions(ElementHelper::modelIteratorToOptions($datagroups))
        )
            ->addHidden('parentId', $parentId)
            ->addAcl('%ADMIN_PERMISSION_ROLES%', 'roles')
            ->addSubmitButton('%CORE_SAVE%');

        return $this;
    }
}
