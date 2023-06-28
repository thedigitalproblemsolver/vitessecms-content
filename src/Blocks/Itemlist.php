<?php declare(strict_types=1);

namespace VitesseCms\Content\Blocks;

use VitesseCms\Block\AbstractBlockModel;
use VitesseCms\Content\Enum\ItemListDisplayOrderingDirectionEnum;
use VitesseCms\Content\Enum\ItemListDisplayOrderingEnum;
use VitesseCms\Content\Enum\ItemListListModeEnum;
use VitesseCms\Block\Models\Block;
use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Database\Utils\MongoUtil;
use MongoDB\BSON\ObjectID;
use function count;
use function in_array;
use function is_array;

//TODO refactor om hem overzichtelijk te maken
class Itemlist extends AbstractBlockModel
{
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
                    Item::setFindValue('parentId', $this->view->getCurrentId());
                endif;
                break;
            default:
                $parseList = true;
                break;
        endswitch;

        if ($parseList) :
            if (is_array($list) && count($list) > 0) :
                $ids = [];
                foreach ($list as $id) :
                    if (MongoUtil::isObjectId($id)) :
                        $ids[] = new ObjectID($id);
                    endif;
                endforeach;
                Item::setFindValue('_id', ['$in' => $ids]);
            endif;

            $this->setItemDefaults($block);
            $items = Item::findAll();

            switch ($block->getString('listMode')):
                case ItemListListModeEnum::LISTMODE_CHILDREN_OF_ITEM->value:
                    $this->parseDatafieldValues($block);
                    $this->setItemDefaults($block);
                    Item::setFindValue('parentId', $block->_('item'));
                    $items = Item::findAll();
                    break;
                case ItemListListModeEnum::LISTMODE_CURRENT_PARENT_CHILDREN->value:
                    if (count($items) === 0) :
                        $currentItem = $this->view->getCurrentItem();
                        Item::setFindValue('parentId', $currentItem->getParentId());
                        $this->setItemDefaults($block);
                        $items = Item::findAll();
                    endif;
                    break;
                case ItemListListModeEnum::LISTMODE_DATAGROUPS->value:
                    $die = true;
                    $ids = [];
                    foreach ($list as $key => $id) :
                        $ids[] = $id;
                    endforeach;
                    Item::setFindValue('datagroup', ['$in' => $ids]);
                    $this->parseDatafieldValues($block);
                    $this->setItemDefaults($block);
                    $items = Item::findAll();
                    break;
                case ItemListListModeEnum::LISTMODE_HANDPICKED->value:
                    $items = [];
                    foreach ($list as $itemId) :
                        $item = Item::findById($itemId);
                        if ($item) :
                            $items[] = $item;
                        endif;
                    endforeach;
                    break;
            endswitch;

            $items = $this->parsedisplayOrderingRandom($items, $block);

            /** @var Item $item */
            foreach ($items as $key => $item) :
                ItemHelper::parseBeforeMainContent($item);
                $items[$key] = $item;
            endforeach;

            $this->parseReadmore($block);
            $block->set('items', $items);
        endif;

        $markerFile = $block->getDi()->get('configuration')->getUploadDir() . 'google-maps-icon-marker.png';
        $markerUrl = $block->getDi()->get('url')->getBaseUri() . 'uploads/' . $block->getDi()->get('configuration')->getAccount() . '/google-maps-icon-marker.png';
        if (is_file($markerFile)) :
            $block->set('googleMapsMarkerIcon', $markerUrl);
        endif;
    }

    private function parsedisplayOrderingRandom(array $items, Block $block) :array
    {
        if($block->getString('displayOrdering') === ItemListDisplayOrderingEnum::RANDOM->value) {
            shuffle($items);
            if ($block->has('numbersToDisplay')) {
                $items = array_slice($items,0,$block->getInt('numbersToDisplay'));
            }
        }

        return $items;
    }

    protected function setItemDefaults(Block $block): void
    {
        if ($block->getString('displayOrdering') !== ItemListDisplayOrderingEnum::RANDOM->value) {
            if($block->has('displayOrderingDirection')) {
                $sort = match ($block->getString('displayOrderingDirection')) {
                    ItemListDisplayOrderingDirectionEnum::OLDEST_FIRST->value => 1,
                    ItemListDisplayOrderingDirectionEnum::NEWEST_FIRST->value => -1,
                    default => 1
                };
                Item::addFindOrder(
                    str_replace(
                        '[]',
                        '.' . $this->getDi()->get('configuration')->getLanguageShort(),
                        $block->getString('displayOrdering')
                    ),
                    $sort
                );
            }
            if ($block->has('numbersToDisplay')) {
                Item::setFindLimit((int)$block->getInt('numbersToDisplay'));
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
                            Item::setFindValue($name, true);
                            break;
                        case 'notSelected':
                            Item::setFindValue($name, ['$in' => ['', false, null]]);
                            break;
                    endswitch;
                elseif (in_array($value, ['bothEmpty', 'empty', 'notEmpty'], true)):
                    switch ($value) :
                        case 'empty':
                            Item::setFindValue($name, null);
                            break;
                        case 'notEmpty':
                            Item::setFindValue($name, ['$nin' => [null, '']]);
                            break;
                    endswitch;
                else :
                    $value = str_replace(
                        ['{{currentId}}'],
                        [$this->view->getCurrentId()],
                        $value
                    );
                    if (trim($value) !== '') :
                        Item::setFindValue($name, ['$in' => explode(',', $value)]);
                    endif;
                endif;
            endforeach;
        endif;
    }

    protected function parseReadmore(Block $block): void
    {
        if ($block->_('readmoreItem')) :
            $item = Item::findById($block->_('readmoreItem'));
            $block->set('readmoreItem', $item);
        endif;
    }

    public function getTemplateParams(Block $block): array
    {
        $params = parent::getTemplateParams($block);
        $params['UPLOAD_URI'] = $this->getDi()->get('configuration')->getUploadUri();
        if(substr_count($this->getTemplate(), 'header_image') > 0 ) {
            if ($this->has('headerImage')) {
                $params['image'] = $this->getDi()->get('configuration')->getUploadUri().$this->has('headerImage');
            } else {
                $params['image'] = $this->getDi()->get('configuration')->getUploadUri().
                    $this->getDi()->get('setting')->getString('HEADER_IMAGE_DEFAULT')
                ;
                $params['imageName'] = $this->getDi()->get('setting')->getString('WEBSITE_DEFAULT_NAME');
            }
        }
        return $params;
    }
}
