<?php declare(strict_types=1);

namespace VitesseCms\Content\Factories;

use VitesseCms\Content\Models\Item;
use VitesseCms\Database\Utils\MongoUtil;

class ItemFactory
{
    public static function create(
        string $title,
        string $datagroupId,
        array $fieldValues = [],
        bool $published = false,
        string $parentId = null,
        int $ordering = 0
    ): Item
    {
        $item = new Item();
        $item->set('name', $title, true);
        $item->setDatagroup($datagroupId);
        $item->setPublished($published);
        $item->setOrdering($ordering);
        $item->setParent($parentId);

        if ($parentId != null && MongoUtil::isObjectId($parentId)) :
            $parentItem = Item::findById($parentId);
            $parentItem->set('hasChildren', true)->save();
        endif;

        foreach ($fieldValues as $fieldName => $fieldParams) :
            $multilang = false;
            if (isset($fieldParams['multilang']) && $fieldParams['multilang'] === true) :
                $multilang = true;
            endif;
            $item->set($fieldName, $fieldParams['value'], $multilang);
        endforeach;

        return $item;
    }
}
