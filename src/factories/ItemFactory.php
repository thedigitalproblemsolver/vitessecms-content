<?php

namespace VitesseCms\Content\Factories;

use VitesseCms\Content\Models\Item;
use VitesseCms\Database\Utils\MongoUtil;

/**
 * Class ItemFactory
 */
class ItemFactory
{
    /**
     * @param string $title
     * @param string $datagroupId
     * @param array $fieldValues
     * @param bool $published
     * @param string|null $parentId
     * @param int $ordering
     *
     * @return Item
     */
    public static function create(
        string $title,
        string $datagroupId,
        array $fieldValues = [],
        bool $published = false,
        string $parentId = null,
        int $ordering = 0
    ): Item {
        $item = new Item();
        $item->set('name', $title, true);
        $item->set('datagroup', $datagroupId);
        $item->set('published', $published);
        $item->set('ordering', $ordering);
        $item->set('parentId', $parentId);

        if( $parentId != null && MongoUtil::isObjectId($parentId)) :
            $parentItem = Item::findById($parentId);
            $parentItem->set('hasChildren',true)->save();
        endif;

        foreach ($fieldValues as $fieldName => $fieldParams) :
            $multilang = false;
            if(isset($fieldParams['multilang']) && $fieldParams['multilang'] === true ) :
                $multilang = true;
            endif;
            $item->set($fieldName, $fieldParams['value'], $multilang);
        endforeach;

        return $item;
    }
}
