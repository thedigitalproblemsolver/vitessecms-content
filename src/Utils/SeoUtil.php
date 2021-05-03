<?php declare(strict_types=1);

namespace VitesseCms\Content\Utils;

use Phalcon\Events\Manager;
use Phalcon\Utils\Slug;
use VitesseCms\Content\Models\Item;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Datafield\Models\Datafield;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Language\Repositories\LanguageRepository;
use VitesseCms\Sef\Helpers\SefHelper;

class SeoUtil
{
    public static function setSlugsOnItem(
        Item $item,
        DatagroupRepository $datagroupRepository,
        DatafieldRepository $datafieldRepository,
        ItemRepository $itemRepository,
        LanguageRepository $languageRepository,
        Manager $eventsManager
    ): Item {
        $datagroup = $datagroupRepository->getById($item->getDatagroup(), false);
        $item->setIsFilterable($datagroup->hasFilterableFields());
        foreach ($datagroup->getDatafields() as $datafieldArray) :
            $datafield = $datafieldRepository->getById($datafieldArray['id']);
            if (is_object($datafield)) :
                $eventsManager->fire($datafield->getFieldType() . ':beforeSave', $item, $datafield);
            endif;
        endforeach;

        $slugCategories = [];
        $slugDatagroups = array_reverse($datagroup->getSlugCategories());
        /** @var Item $previousItem */
        $previousItem = clone $item;
        foreach ($slugDatagroups as $datagroupArray) :
            if (is_array($datagroupArray) && $previousItem) :
                if ($datagroupArray['published'] && $previousItem->getParentId() !== null):
                    $slugCategories[] = $itemRepository->getById($previousItem->getParentId(), false);
                endif;
            endif;
        endforeach;
        $slugCategories = array_reverse($slugCategories);

        $slugDatafields = [];
        foreach ($datagroup->getSlugDatafields() as $datafieldArray) :
            if ($datafieldArray['published']) :
                $slugDatafields[] = $datafieldRepository->getById($datafieldArray['id']);
            endif;
        endforeach;

        $slugs = [];
        $languages = $languageRepository->findAll(null, false);
        while ($languages->valid()) :
            $language = $languages->current();
            if (!isset($slugs[$language->getShortCode()])) :
                $slugs[$language->getShortCode()] = '';
            endif;

            $slugParts = [];
            if (count($slugCategories) > 0):
                /** @var AbstractCollection $slugCategory */
                foreach ($slugCategories as $slugCategory) :
                    $slugParts[] = Slug::generate($slugCategory->_('name', $language->getShortCode()));
                endforeach;
                $slugs[$language->getShortCode()] = implode($datagroup->getSlugDelimiter(), $slugParts);
                if (substr_count($slugs[$language->getShortCode()], 'page:') === 0) :
                    $slugs[$language->getShortCode()] .= '/';
                endif;
            endif;

            $slugParts = [];
            /** @var Datafield $datafield */
            foreach ($slugDatafields as $datafield) :
                $datafieldResult = $datafield->getSlugPart($item, $language->getShortCode());
                if (!empty($datafieldResult)) :
                    $slugParts[] = $datafieldResult;
                endif;
            endforeach;

            $slugs[$language->getShortCode()] .= implode($datagroup->getSlugDelimiter(), $slugParts);
            if (substr_count($slugs[$language->getShortCode()], 'page:') === 0) :
                $slugs[$language->getShortCode()] .= '/';
            endif;
            $languages->next();
        endwhile;

        $item->setSlugs($slugs);
        SefHelper::saveRedirectFromItem((string)$item->getId(), $slugs);

        return $item;
    }
}