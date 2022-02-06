<?php declare(strict_types=1);

namespace VitesseCms\Content\Forms;

use VitesseCms\Block\Forms\BlockForm;
use VitesseCms\Block\Interfaces\BlockSubFormInterface;
use VitesseCms\Block\Models\Block;
use VitesseCms\Block\Repositories\RepositoryCollection;
use VitesseCms\Content\Repositories\AdminRepositoryCollection;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Form\Models\Attributes;

class BlockItemlistChildrenOfItemSubForm extends AbstractBlockItemlistSubForm implements BlockSubFormInterface
{
    public function getBlockForm(BlockForm $form, Block $block): void
    {
        $selectedItem = null;

        $options = [[
            'value' => '',
            'label' => '%ADMIN_TYPE_TO_SEARCH%',
            'selected' => false,
        ]];
        if ($block->_('item')) :
            $selectedItem = $this->itemRepository->getById($block->_('item'));
            $itemPath = ItemHelper::getPathFromRoot($selectedItem);
            $options[] = [
                'value' => (string)$selectedItem->getId(),
                'label' => implode(' - ', $itemPath),
                'selected' => true,

            ];
        endif;

        $form->addDropdown(
            '%ADMIN_ITEMS%',
            'item',
            (new Attributes())
                ->setOptions($options)
                ->setInputClass('select2-ajax')
                ->setDataUrl('/content/index/search/')
        );

        if ($selectedItem !== null) :
            $datagroupChildren = $this->datagroupRepository->findAll(new FindValueIterator(
                [new FindValue('parentId', $selectedItem->getDatagroup())]
            ));
            while ($datagroupChildren->valid()) :
                $datagroupChild = $datagroupChildren->current();
                $this->buildDatafieldValueForm($form, (string)$datagroupChild->getId());
                $datagroupChildren->next();
            endwhile;
        endif;
    }
}
