<?php declare(strict_types=1);

namespace VitesseCms\Content\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Content\Forms\NewItemForm;
use VitesseCms\Content\Interfaces\AdminRepositoriesInterface;
use VitesseCms\Content\Models\Item;
use VitesseCms\Content\Forms\ItemForm;
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
        string $templatePath = 'src/core/resources/views/admin/',
        AbstractForm $form = null
    ): void {
        if ($itemId === null) :
            $parentId = $this->request->get('parentId', null);
            parent::editAction($itemId, $template, $templatePath, (new NewItemForm())
                ->setRepositories($this->repositories)
                ->setParentId($parentId)
                ->build()
            );
        else :
            parent::editAction($itemId, $template, $templatePath, (new ItemForm())
                ->setRepositories($this->repositories)
                ->build($this->repositories->item->getById($itemId, false))
            );
        endif;
    }

    public function saveAction(?string $itemId = null, AbstractCollection $item = null, AbstractForm $form = null): void
    {
        if ($itemId === null) :
            parent::saveAction(
                $itemId,
                null,
                (new NewItemForm())
                    ->setRepositories($this->repositories)
                    ->build()
            );
        else :
            $item = $this->repositories->item->getById($itemId, false, false);
            parent::saveAction(
                $itemId,
                null,
                (new ItemForm())
                    ->setRepositories($this->repositories)
                    ->build($item)
            );
        endif;
    }
}
