<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use Phalcon\Events\Event;
use Phalcon\Http\Request;
use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Admin\Forms\AdminlistFormInterface;
use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Interfaces\BaseObjectInterface;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Datafield\Models\Datafield;
use VitesseCms\Datagroup\Helpers\DatagroupHelper;
use VitesseCms\Datagroup\Models\Datagroup;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Interfaces\AbstractFormInterface;
use VitesseCms\Form\Models\Attributes;

class AdminItemControllerListener
{
    public function adminListFilter(
        Event $event,
        AbstractAdminController $controller,
        AdminlistFormInterface $form
    ): string
    {
        $form->addHidden('filter[datagroup]');

        $request = new Request();
        if (isset($request->get('filter')['datagroup'])) :
            $mainDatagroup = Datagroup::findById($request->get('filter')['datagroup']);
            $datagroups = DatagroupHelper::getChildrenFromRoot($mainDatagroup);
            /** @var Datagroup $datagroup */
            foreach ($datagroups as $datagroup) :
                foreach ($datagroup->getDatafields() as $datafield) :
                    if ($datafield['published']) :
                        $datafield = Datafield::findById($datafield['id']);
                        if ($datafield && $datafield->isPublished()) :
                            $datafield->renderAdminlistFilter($form);
                        endif;
                    endif;
                endforeach;
            endforeach;

            $form->addDropdown(
                'Has as parent',
                'filter[parentId]',
                (new Attributes())->setOptions(
                    ElementHelper::arrayToSelectOptions($this->getParentOptionsFromDatagroup($mainDatagroup))
                )
            );

        endif;

        $form->addPublishedField($form);

        return $form->renderForm(
            $controller->getLink() . '/' . $controller->router->getActionName(),
            'adminFilter'
        );
    }

    protected function getParentOptionsFromDatagroup(Datagroup $datagroup, array $parentOptions = []): array
    {
        Item::setFindValue('datagroup', (string)$datagroup->getId());
        $items = Item::findAll();
        foreach ($items as $item) :
            if ($item->hasChildren()):
                $parentOptions[(string)$item->getId()] = $item->_('name');
                $this->getParentOptionsFromItem($item, $parentOptions);
            endif;
        endforeach;

        return $parentOptions;
    }

    protected function getParentOptionsFromItem(
        AbstractCollection $parent,
        array &$parentOptions = [],
        array $prefix = []
    ): void {
        Item::setFindValue('parentId', (string)$parent->getId());
        $prefix[] = $parent->_('name');
        $items = Item::findAll();
        foreach ($items as $item) :
            if ($item->hasChildren()):
                $parentOptions[(string)$item->getId()] = implode(' > ', $prefix).' > '.$item->_('name');
                $this->getParentOptionsFromItem($item, $parentOptions, $prefix);
            endif;
        endforeach;
    }
}
