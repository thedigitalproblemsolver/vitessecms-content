<?php declare(strict_types=1);

namespace VitesseCms\Content\Enum;

use VitesseCms\Form\Interfaces\SelectOptionEnumInterface;

enum ItemListDisplayOrderingDirectionEnum: string implements SelectOptionEnumInterface
{
    case OLDEST_FIRST = 'oldest';
    case NEWEST_FIRST = 'newest';

    public static function getLabel( $label): string
    {
        return match ($label) {
            self::OLDEST_FIRST => '%CONTENT_OLDEST_FIRST%',
            self::NEWEST_FIRST => '%CONTENT_NEWEST_FIRST%',
            default => throw new \Exception('Unexpected match value')
        };
    }
}
