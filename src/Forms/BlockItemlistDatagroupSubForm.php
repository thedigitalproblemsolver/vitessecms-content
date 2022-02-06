<?php declare(strict_types=1);

namespace VitesseCms\Content\Forms;

use VitesseCms\Block\Forms\BlockForm;
use VitesseCms\Block\Interfaces\BlockSubFormInterface;
use VitesseCms\Block\Models\Block;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;

class BlockItemlistDatagroupSubForm extends AbstractBlockItemlistSubForm implements BlockSubFormInterface
{
    public function getBlockForm(BlockForm $form, Block $block): void
    {
        $form->addDropdown(
            '%ADMIN_ITEMS%',
            'items',
            (new Attributes())
                ->setMultiple(true)
                ->setInputClass('select2')
                ->setOptions(ElementHelper::modelIteratorToOptions($this->datagroupRepository->findAll()))
        );

        if (is_array($block->_('items'))) :
            foreach ($block->_('items') as $datagroupId) :
                $this->buildDatafieldValueForm($form, $datagroupId);
            endforeach;
            $form->addText('parentId', 'datafieldValue[parentId]');
        endif;
    }
}
