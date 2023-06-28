<?php declare(strict_types=1);

namespace VitesseCms\Content\Enum;

use VitesseCms\Form\Interfaces\SelectOptionEnumInterface;

enum ItemListListModeEnum: string implements SelectOptionEnumInterface
{
    case LISTMODE_CURRENT = 'current';
    case LISTMODE_CHILDREN_OF_ITEM = 'childrenOfItem';
    case LISTMODE_CURRENT_CHILDREN = 'currentChildren';
    case LISTMODE_CURRENT_PARENT_CHILDREN = 'currentParentChildren';
    case LISTMODE_CURRENT_CHILDREN_OF_PARENT = 'currentChildrenOfParent';
    case LISTMODE_HANDPICKED = 'handpicked';
    case LISTMODE_DATAGROUPS = 'datagroups';

    public static function getLabel( $label): string
    {
        return match ($label) {
            self::LISTMODE_CURRENT => '%ADMIN_ITEMLIST_MODE_CURRENT_ITEM%',
            self::LISTMODE_CHILDREN_OF_ITEM => '%ADMIN_ITEMLIST_MODE_CHILDREN_OF_ITEM%',
            self::LISTMODE_CURRENT_CHILDREN => '%ADMIN_ITEMLIST_MODE_CHILDREN_OF_CURRENT_ITEM%',
            self::LISTMODE_CURRENT_PARENT_CHILDREN => '%ADMIN_ITEMLIST_MODE_CHILDREN_OF_ITEM_OR_SELECTED%',
            self::LISTMODE_CURRENT_CHILDREN_OF_PARENT => '%ADMIN_ITEMLIST_MODE_CHILDREN_FROM_PARENT_OF_ITEM_OR_SELECTED%',
            self::LISTMODE_HANDPICKED => '%ADMIN_ITEMLIST_MODE_HANDPICKED_ITEMS%',
            self::LISTMODE_DATAGROUPS => '%ADMIN_ITEMLIST_MODE_DATAGROUP%',
            default => throw new \Exception('Unexpected match value')
        };
    }
}
