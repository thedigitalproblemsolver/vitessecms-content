<?php declare(strict_types=1);

namespace VitesseCms\Content\Forms;

use VitesseCms\Block\Forms\BlockForm;
use VitesseCms\Block\Interfaces\BlockSubFormInterface;
use VitesseCms\Block\Models\Block;
use VitesseCms\Block\Repositories\AdminRepositoryCollection;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Form\Models\Attributes;

class BlockItemlistChildrenOfItemSubForm extends AbstractBlockItemlistSubForm implements BlockSubFormInterface
{
    public static function getBlockForm(BlockForm $form, Block $block, AdminRepositoryCollection $repositories): void
    {
        $selectedItem = null;

        $options = [[
            'value' => '',
            'label' => '%ADMIN_TYPE_TO_SEARCH%',
            'selected' => false,
        ]];
        if ($block->_('item')) :
            $selectedItem = $repositories->item->getById($block->_('item'));
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
            $datagroupChildren = $repositories->datagroup->findAll(new FindValueIterator(
                [new FindValue('parentId', $selectedItem->getDatagroup())]
            ));
            while ($datagroupChildren->valid()) :
                $datagroupChild = $datagroupChildren->current();
                self::buildDatafieldValueForm($form, (string)$datagroupChild->getId(), $repositories);
                $datagroupChildren->next();
            endwhile;
        endif;
    }
}
