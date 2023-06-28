<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners\Blocks;

use Phalcon\Events\Event;
use VitesseCms\Block\Forms\BlockForm;
use VitesseCms\Block\Models\Block;
use VitesseCms\Content\Blocks\Itemlist;
use VitesseCms\Content\Enum\ItemListListModeEnum;
use VitesseCms\Content\Forms\BlockItemlistChildrenOfItemSubForm;
use VitesseCms\Content\Forms\BlockItemlistDatagroupSubForm;
use VitesseCms\Content\Forms\BlockItemlistHandpickedSubForm;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;

class BlockItemlistListener
{
    public function __construct(
        private readonly ItemRepository $itemRepository,
        private readonly DatagroupRepository $datagroupRepository,
        private readonly DatafieldRepository $datafieldRepository
    ){
    }

    public function buildBlockForm(Event $event, BlockForm $form, Block $block): void
    {
        $this->buildBaseForm($form, $block);
        $this->buildListModeForm($form, $block);
    }

    private function buildBaseForm(BlockForm $form, Block $block): void
    {
        $form->addDropdown(
            '%ADMIN_SOURCE%',
            'listMode',
            (new Attributes())->setOptions(ElementHelper::EnumToSelectOptions(ItemListListModeEnum::cases()))
        )->addDropdown(
            '%ADMIN_ITEM_ORDER_DISPLAY%',
            'displayOrdering',
            (new Attributes())->setOptions(ElementHelper::arrayToSelectOptions([
                'ordering' => '%ADMIN_ITEM_ORDER_ORDERING%',
                'name[]' => '%ADMIN_ITEM_ORDER_NAME%',
                'createdAt' => '%ADMIN_ITEM_ORDER_CREATED%',
            ]))
        )->addDropdown(
            'Volgorde sortering ',
            'displayOrderingDirection',
            (new Attributes())->setOptions(ElementHelper::arrayToSelectOptions([
                'oldest' => 'oldest first, A > Z',
                'newest' => 'newest first, Z > A',
            ]))
        )->addNumber('%ADMIN_ITEM_ORDER_DISPLAY_NUMBER%', 'numbersToDisplay')
            ->addText(
                '%ADMIN_READMORE_TEXT%',
                'readmoreText',
                (new Attributes())->setMultilang(true)
            );

        $options = [[
            'value' => '',
            'label' => '%ADMIN_TYPE_TO_SEARCH%',
            'selected' => false,
        ]];
        if ($block->_('readmoreItem')) :
            $selectedItem = $this->itemRepository->getById($block->_('readmoreItem'));
            if ($selectedItem !== null):
                $itemPath = ItemHelper::getPathFromRoot($selectedItem);
                $options[] = [
                    'value' => (string)$selectedItem->getId(),
                    'label' => implode(' - ', $itemPath),
                    'selected' => true,
                ];
            endif;
        endif;
        $form->addDropdown(
            '%ADMIN_READMORE_PAGE%',
            'readmoreItem',
            (new Attributes())
                ->setOptions($options)
                ->setInputClass('select2-ajax')
                ->setDataUrl('/content/index/search/')
        )->addToggle('%ADMIN_READMORE_SHOW_PER_ITEM%', 'readmoreShowPerItem');
    }

    private function buildListModeForm(BlockForm $form, Block $block): void
    {
        switch ($block->_('listMode')) :
            case ItemListListModeEnum::LISTMODE_HANDPICKED->value:
                (new BlockItemlistHandpickedSubForm(
                    $this->itemRepository,
                    $this->datagroupRepository,
                    $this->datafieldRepository
                ))->getBlockForm($form, $block);
                break;
            case ItemListListModeEnum::LISTMODE_CHILDREN_OF_ITEM->value:
                (new BlockItemlistChildrenOfItemSubForm(
                    $this->itemRepository,
                    $this->datagroupRepository,
                    $this->datafieldRepository
                ))->getBlockForm($form, $block);
                break;
            case ItemListListModeEnum::LISTMODE_DATAGROUPS->value:
                (new BlockItemlistDatagroupSubForm(
                    $this->itemRepository,
                    $this->datagroupRepository,
                    $this->datafieldRepository
                ))->getBlockForm($form, $block);
                break;
        endswitch;

        if (
            substr_count($block->getTemplate(), 'card_two_columns')
            || substr_count($block->getTemplate(), 'card_three_columns')
            || substr_count($block->getTemplate(), 'card_four_columns')
        ):
            $form->addToggle('Image fullwidth', 'imageFullWidth')
                ->addToggle('Hide intro text', 'hideIntroText');
        endif;
    }

    public function loadAssets(Event $event, Itemlist $itemlist, Block $block): void
    {
        if (
            substr_count($block->getTemplate(), 'address_list')
            && $block->getDi()->setting->has('GOOGLE_MAPS_APIKEY')
        ) :
            $block->getDI()->get('assets')->loadGoogleMaps(
                $block->getDi()->setting->get('GOOGLE_MAPS_APIKEY')
            );
        endif;
    }
}