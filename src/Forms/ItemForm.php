<?php

declare(strict_types=1);

namespace VitesseCms\Content\Forms;

use stdClass;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Datagroup\Enums\DatagroupEnum;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Form\AbstractFormWithRepository;
use VitesseCms\Form\Interfaces\FormWithRepositoryInterface;

class ItemForm extends AbstractFormWithRepository
{
    private DatagroupRepository $datagroupRepository;

    public function __construct($entity = null, array $userOptions = [])
    {
        parent::__construct($entity, $userOptions);

        $this->datagroupRepository = $this->eventsManager->fire(DatagroupEnum::GET_REPOSITORY->value, new stdClass());
    }

    public function buildForm(): FormWithRepositoryInterface
    {
        $datagroup = $this->datagroupRepository->getById($this->entity->getDatagroup(), false);
        if ($datagroup !== null) {
            $breadcrumbItems = ItemHelper::getPathFromRoot($this->entity);
            $breadcrumbs = [];
            foreach ($breadcrumbItems as $breadcrumbItem) :
                $breadcrumbs[] = '<a href="admin/content/adminitem/adminList/?filter[datagroup]=' . $breadcrumbItem->getDatagroup(
                    ) . '" target="_blank">' . $breadcrumbItem->getNameField() . '</a>';
            endforeach;
            $this->addHtml('Breadcrumbs: ' . implode(' > ', $breadcrumbs));
            $datagroup->buildItemForm($this, $this->entity);

            $this->addNumber('%ADMIN_ORDERING%', 'ordering')
                ->addAcl('%ADMIN_PERMISSION_ROLES%', 'roles')
                ->addSubmitButton('%CORE_SAVE%');
            if ($this->entity !== null) :
                $this->addHtml(
                    '<a 
                    href="' . $this->url->getBaseUri(
                    ) . 'admin/content/adminitem/delete/' . (string)$this->entity->getId() . '" 
                    id="delete_' . (string)$this->entity->getId() . '" 
                    class="fa fa-trash" 
                    title="Verwijder item"
                ></a>'
                );
            endif;
        }

        return $this;
    }
}
