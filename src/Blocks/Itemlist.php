<?php declare(strict_types=1);

namespace VitesseCms\Content\Blocks;

use Phalcon\Di\Di;
use Phalcon\Http\Request;
use VitesseCms\Admin\Helpers\PaginationHelper;
use VitesseCms\Block\AbstractBlockModel;
use VitesseCms\Configuration\Enums\ConfigurationEnum;
use VitesseCms\Configuration\Services\ConfigService;
use VitesseCms\Content\Enum\ItemEnum;
use VitesseCms\Content\Enum\ItemListDisplayOrderingDirectionEnum;
use VitesseCms\Content\Enum\ItemListDisplayOrderingEnum;
use VitesseCms\Content\Enum\ItemListListModeEnum;
use VitesseCms\Block\Models\Block;
use VitesseCms\Content\Models\Item;
use VitesseCms\Content\Models\ItemIterator;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\Enum\UrlEnum;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Core\Services\UrlService;
use VitesseCms\Core\Services\ViewService;
use VitesseCms\Database\Models\FindOrder;
use VitesseCms\Database\Models\FindOrderIterator;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Database\Utils\MongoUtil;
use MongoDB\BSON\ObjectID;
use VitesseCms\Setting\Enum\SettingEnum;
use VitesseCms\Setting\Services\SettingService;
use function count;
use function in_array;
use function is_array;

//TODO refactor om hem overzichtelijk te maken
class Itemlist extends AbstractBlockModel
{
    private readonly UrlService $urlService;
    private readonly ItemRepository $itemRepository;
    private readonly FindValueIterator $findValueIterator;
    private readonly FindOrderIterator $findOrderIterator;
    private readonly SettingService $settingService;
    private readonly ConfigService $configService;
    private ?int $findLimit;
    private readonly Request $request;

    public function __construct(ViewService $view, Di $di)
    {
        parent::__construct($view, $di);
        $this->urlService = $di->get('eventsManager')->fire(UrlEnum::ATTACH_SERVICE_LISTENER, new \stdClass());
        $this->settingService = $di->get('eventsManager')->fire(SettingEnum::ATTACH_SERVICE_LISTENER->value, new \stdClass());
        $this->itemRepository = $di->get('eventsManager')->fire(ItemEnum::GET_REPOSITORY, new \stdClass());
        $this->configService = $di->get('eventsManager')->fire(ConfigurationEnum::ATTACH_SERVICE_LISTENER->value, new \stdClass());
        $this->request = $di->get('request');
        $this->findValueIterator = new FindValueIterator();
        $this->findOrderIterator = new FindOrderIterator();
        $this->findLimit = null;
    }

    public function parse(Block $block): void
    {
        parent::parse($block);

        $parseList = true;
        $list = $block->_('items');

        switch ($block->getString('listMode')) :
            case ItemListListModeEnum::LISTMODE_CURRENT->value:
                if ($this->view->hasCurrentItem()) :
                    $list = [$this->view->getCurrentId()];
                else :
                    $parseList = false;
                endif;
                break;
            case ItemListListModeEnum::LISTMODE_CHILDREN_OF_ITEM->value:
                $list = [$block->_('item')];
                break;
            case ItemListListModeEnum::LISTMODE_CURRENT_CHILDREN->value:
            case ItemListListModeEnum::LISTMODE_CURRENT_PARENT_CHILDREN->value:
                if ($this->view->hasCurrentItem()) :
                    $this->findValueIterator->add(new FindValue('parentId', $this->view->getCurrentId()));
                endif;
                break;
            default:
                $parseList = true;
                break;
        endswitch;

        if ($parseList) :
            if (is_array($list) && count($list) > 0) {
                $ids = [];
                foreach ($list as $id) {
                    if (MongoUtil::isObjectId($id)) {
                        $ids[] = new ObjectID($id);
                    }
                }
                $this->findValueIterator->add(new FindValue('_id', ['$in' => $ids]));
            }

            $this->setItemDefaults($block);
            $items = $this->getItems();

            switch ($block->getString('listMode')):
                case ItemListListModeEnum::LISTMODE_CHILDREN_OF_ITEM->value:
                    $this->parseDatafieldValues($block);
                    $this->setItemDefaults($block);
                    $this->findValueIterator->add(new FindValue('parentId', $block->_('item')));
                    $items = $this->getItems();
                    break;
                case ItemListListModeEnum::LISTMODE_CURRENT_PARENT_CHILDREN->value:
                    if (count($items) === 0) :
                        $currentItem = $this->view->getCurrentItem();
                        $this->findValueIterator->add(new FindValue('parentId', $currentItem->getParentId()));
                        $this->setItemDefaults($block);
                        $items = $this->getItems();
                    endif;
                    break;
                case ItemListListModeEnum::LISTMODE_DATAGROUPS->value:
                    $die = true;
                    $ids = [];
                    foreach ($list as $key => $id) :
                        $ids[] = $id;
                    endforeach;
                    $this->findValueIterator->add(new FindValue('datagroup', ['$in' => $ids]));
                    $this->parseDatafieldValues($block);
                    $this->setItemDefaults($block);
                    $items = $this->getItems();
                    break;
                case ItemListListModeEnum::LISTMODE_HANDPICKED->value:
                    $items = new ItemIterator([]);
                    foreach ($list as $itemId) :
                        $item = $this->itemRepository->getById($itemId);
                        if ($item !== null) :
                            $items->add($item);
                        endif;
                    endforeach;
                    break;
            endswitch;

            $items = $this->parsedisplayOrderingRandom($items, $block);

            while ($items->valid()) {
                ItemHelper::parseBeforeMainContent($items->current());
                $items->next();
            }

            $this->parseReadmore($block);
            if($block->has('itemsOnPage')) {
                $pagination = new PaginationHelper($items, $this->urlService, $this->request->get('offset','int', 0), $block->getInt('itemsOnPage'));
                $block->set('items', $pagination->getSliced());
                $block->set('pagination', $pagination);
            } else {
                $block->set('items', $items);
            }
        endif;
    }

    private function getItems(): ?ItemIterator
    {
        return $this->itemRepository->findAll(
            $this->findValueIterator,
            true,
            $this->findLimit,
            $this->findOrderIterator
        );
    }

    private function parsedisplayOrderingRandom(ItemIterator $items, Block $block) :ItemIterator
    {
        if($block->getString('displayOrdering') === ItemListDisplayOrderingEnum::RANDOM->value) {
            $itemsArray = [];
            while ($items->valid()) {
                $itemsArray[] = $items->current();
                $items->next();
            }
            shuffle($itemsArray);
            if ($block->has('numbersToDisplay')) {
                $itemsArray = array_slice($itemsArray,0,$block->getInt('numbersToDisplay'));
            }

            return new ItemIterator($itemsArray);
        }

        return $items;
    }

    protected function setItemDefaults(Block $block): void
    {
        if ($block->getString('displayOrdering') !== ItemListDisplayOrderingEnum::RANDOM->value) {
            if($block->has('displayOrderingDirection')) {
                $sort = match ($block->getString('displayOrderingDirection')) {
                    ItemListDisplayOrderingDirectionEnum::NEWEST_FIRST->value => -1,
                    default => 1
                };
                $this->findOrderIterator->add(
                    new FindOrder(
                        str_replace(
                            '[]',
                            '.' . $this->getDi()->get('configuration')->getLanguageShort(),
                            $block->getString('displayOrdering')
                        ),
                        $sort
                    )
                );
            }
            if ($block->has('numbersToDisplay')) {
                $this->findLimit = $block->getInt('numbersToDisplay');
            };
        }
    }

    protected function parseDatafieldValues(Block $block): void
    {
        if (
            is_array($block->_('datafieldValue'))
            && count($block->_('datafieldValue')) > 0
        ) :
            foreach ($block->_('datafieldValue') as $name => $value):
                if (in_array($value, ['both', 'selected', 'notSelected'], true)) :
                    switch ($value) :
                        case 'selected':
                            $this->findValueIterator->add(new FindValue($name, true));
                            break;
                        case 'notSelected':
                            $this->findValueIterator->add(new FindValue($name, ['$in' => ['', false, null]]));
                            break;
                    endswitch;
                elseif (in_array($value, ['bothEmpty', 'empty', 'notEmpty'], true)):
                    switch ($value) :
                        case 'empty':
                            $this->findValueIterator->add(new FindValue($name, null));
                            break;
                        case 'notEmpty':
                            $this->findValueIterator->add(new FindValue($name, ['$nin' => [null, '']]));
                            break;
                    endswitch;
                else :
                    $value = str_replace(['{{currentId}}'], [$this->view->getCurrentId()], $value);
                    if (trim($value) !== '') :
                        $this->findValueIterator->add(new FindValue($name, ['$in' => explode(',', $value)]));
                    endif;
                endif;
            endforeach;
        endif;
    }

    protected function parseReadmore(Block $block): void
    {
        if ($block->has('readmoreItem')) :
            $block->set('readmoreItem', $this->itemRepository->getById($block->getString('readmoreItem')));
        endif;
    }

    public function getTemplateParams(Block $block): array
    {
        $params = parent::getTemplateParams($block);
        $params['UPLOAD_URI'] = $this->configService->getUploadUri();
        if(substr_count($this->getTemplate(), 'header_image') > 0 ) {
            if ($this->has('headerImage')) {
                $params['image'] = $this->configService->getUploadUri().$this->getString('headerImage');
            } elseif($this->settingService->has('HEADER_IMAGE_DEFAULT')) {
                $params['image'] = $this->configService->getUploadUri().
                    $this->settingService->getString('HEADER_IMAGE_DEFAULT')
                ;
                $params['imageName'] = $this->settingService->getString('WEBSITE_DEFAULT_NAME');
            }
        }

        if($block->has('pagination')) {
            $params['pagination'] = $block->_('pagination');
        }

        return $params;
    }
}
