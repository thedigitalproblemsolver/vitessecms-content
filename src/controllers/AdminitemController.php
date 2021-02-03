<?php declare(strict_types=1);

namespace VitesseCms\Content\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Content\Forms\NewItemForm;
use VitesseCms\Content\Models\Item;
use VitesseCms\Content\Forms\ItemForm;
use VitesseCms\Content\Repositories\AdminRepositoriesInterface;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Form\AbstractForm;

class AdminitemController extends AbstractAdminController implements AdminRepositoriesInterface
{
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = Item::class;
        $this->classForm = ItemForm::class;
        $this->listNestable = true;
        $this->listSortable = true;
    }

    public function editAction(
        string $itemId = null,
        string $template = 'editForm',
        string $templatePath = 'core/src/resources/views/admin/',
        AbstractForm $form = null
    ): void {
        if ($itemId === null) :
            $this->classForm = NewItemForm::class;
        endif;

        parent::editAction($itemId);
    }

    public function saveAction(?string $itemId = null, AbstractCollection $item = null, AbstractForm $form = null): void
    {
        if ($itemId === null) :
            $this->classForm = NewItemForm::class;
        endif;

        parent::saveAction($itemId);
    }
}
