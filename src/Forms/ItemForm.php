<?php declare(strict_types=1);

namespace VitesseCms\Content\Forms;

use VitesseCms\Content\Models\Item;
use VitesseCms\Content\Repositories\AdminRepositoryCollection;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Form\AbstractFormWithRepository;
use VitesseCms\Form\Interfaces\FormWithRepositoryInterface;

class ItemForm extends AbstractFormWithRepository
{
    /**
     * @var AdminRepositoryCollection
     */
    protected $repositories;

    /**
     * @var Item
     */
    protected $_entity;

    public function buildForm(): FormWithRepositoryInterface
    {
        $datagroup = $this->repositories->datagroup->getById($this->_entity->getDatagroup(), false);
        if ($datagroup !== null) {
            $breadcrumbItems = ItemHelper::getPathFromRoot($this->_entity);
            $breadcrumbs = [];
            foreach ($breadcrumbItems as $breadcrumbItem) :
                $breadcrumbs[] = '<a href="admin/content/adminitem/adminList/?filter[datagroup]=' . $breadcrumbItem->getDatagroup() . '" target="_blank">' . $breadcrumbItem->getNameField() . '</a>';
            endforeach;
            $this->addHtml('Breadcrumbs: ' . implode($breadcrumbs, ' > '));
            $datagroup->buildItemForm($this, $this->_entity);

            $this->addNumber('%ADMIN_ORDERING%', 'ordering')
                ->addAcl('%ADMIN_PERMISSION_ROLES%', 'roles')
                ->addSubmitButton('%CORE_SAVE%');
            if($this->_entity !== null) :
                $this->addHtml('<a 
                    href="'.$this->url->getBaseUri().'admin/content/adminitem/delete/'.(string)$this->_entity->getId().'" 
                    id="delete_'.(string)$this->_entity->getId().'" 
                    class="fa fa-trash" 
                    title="Verwijder item"
                ></a>');
            endif;
        }

        return $this;
    }
}
