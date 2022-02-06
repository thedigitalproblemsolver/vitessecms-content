<?php declare(strict_types=1);

namespace VitesseCms\Content\Forms;

use VitesseCms\Block\Forms\BlockForm;
use VitesseCms\Block\Interfaces\BlockSubFormInterface;
use VitesseCms\Block\Models\Block;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;

class BlockItemlistHandpickedSubForm extends AbstractBlockItemlistSubForm implements BlockSubFormInterface
{
    public function getBlockForm(BlockForm $form, Block $block): void
    {
        $datagroupIds = [];
        $datagroups = $this->datagroupRepository->findAll(new FindValueIterator(
            [new FindValue('component', ['$in' => ['content', 'webshopProduct']])]
        ));

        while ($datagroups->valid()) :
            $datagroup = $datagroups->current();
            $datagroupIds[] = (string)$datagroup->getId();
            $datagroups->next();
        endwhile;

        $form->addDropdown(
            '%ADMIN_ITEMS%',
            'items',
            (new Attributes())
                ->setMultiple(true)
                ->setOptions(ElementHelper::modelIteratorToOptions($this->itemRepository->findAll(
                    new FindValueIterator(
                        [new FindValue('datagroup', ['$in' => $datagroupIds])]
                    ),
                    true,
                    999
                )))
                ->setInputClass('select2-sortable')
        );
    }
}
