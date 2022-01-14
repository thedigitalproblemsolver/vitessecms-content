<?php declare(strict_types=1);

namespace VitesseCms\Content\Forms;

use VitesseCms\Content\Repositories\AdminRepositoryCollection;
use VitesseCms\Content\Models\Item;
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
            $datagroup->buildItemForm($this, $this->_entity);

            $this->addNumber('%ADMIN_ORDERING%', 'ordering')
                ->addAcl('%ADMIN_PERMISSION_ROLES%', 'roles')
                ->addSubmitButton('%CORE_SAVE%');
        }

        return $this;
    }
}
