<?php declare(strict_types=1);

namespace VitesseCms\Content\Enum;

use VitesseCms\Form\Interfaces\SelectOptionEnumInterface;

enum ItemListDisplayOrderingEnum: string implements SelectOptionEnumInterface
{
    case BY_ORDER = 'ordering';
    case BY_NAME = 'name[]';
    case BY_CREATED_AT = 'createdAt';

    public static function getLabel( $label): string
    {
        return match ($label) {
            self::BY_ORDER => '%ADMIN_ITEM_ORDER_ORDERING%',
            self::BY_NAME => '%ADMIN_ITEM_ORDER_NAME%',
            self::BY_CREATED_AT => '%ADMIN_ITEM_ORDER_CREATED%',
            default => throw new \Exception('Unexpected match value')
        };
    }
}
