<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners\Blocks;

use Phalcon\Events\Event;
use VitesseCms\Block\Forms\BlockForm;
use VitesseCms\Block\Models\Block;
use VitesseCms\Content\Blocks\Itemlist;
use VitesseCms\Content\Enum\ItemListEnum;
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
    /**
     * @var ItemRepository
     */
    private $itemRepository;

    /**
     * @var DatagroupRepository
     */
    private $datagroupRepository;

    /**
     * @var DatafieldRepository
     */
    private $datafieldRepository;

    public function __construct(
        ItemRepository $itemRepository,
        DatagroupRepository $datagroupRepository,
        DatafieldRepository $datafieldRepository
    ){
        $this->itemRepository = $itemRepository;
        $this->datagroupRepository = $datagroupRepository;
        $this->datafieldRepository  =$datafieldRepository;
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
            (new Attributes())->setOptions(ElementHelper::arrayToSelectOptions(ItemListEnum::LISTMODES))
        )->addDropdown(
            '%ADMIN_ITEM_ORDER_DISPLAY%',
            'displayOrdering',
            (new Attributes())->setOptions(ElementHelper::arrayToSelectOptions([
                'ordering' => '%ADMIN_ITEM_ORDER_ORDERING%',
                'name' => '%ADMIN_ITEM_ORDER_NAME%',
                'createdAt' => '%ADMIN_ITEM_ORDER_CREATED%',
            ]))
        )->addDropdown(
            'Volgorde sortering ',
            'displayOrderingDirection',
            (new Attributes())->setOptions(ElementHelper::arrayToSelectOptions([
                'oldest' => 'oldest first',
                'newest' => 'newest first',
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
            case ItemListEnum::LISTMODE_HANDPICKED:
                (new BlockItemlistHandpickedSubForm(
                    $this->itemRepository,
                    $this->datagroupRepository,
                    $this->datafieldRepository
                ))->getBlockForm($form, $block);
                break;
            case ItemListEnum::LISTMODE_CHILDREN_OF_ITEM:
                (new BlockItemlistChildrenOfItemSubForm(
                    $this->itemRepository,
                    $this->datagroupRepository,
                    $this->datafieldRepository
                ))->getBlockForm($form, $block);
                break;
            case ItemListEnum::LISTMODE_DATAGROUPS:
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