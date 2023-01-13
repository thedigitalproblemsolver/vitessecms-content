<?php declare(strict_types=1);

namespace VitesseCms\Content\Utils;

use Phalcon\Events\Manager;
use VitesseCms\Content\Models\Item;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Datafield\Models\Datafield;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Datagroup\Models\Datagroup;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Language\Repositories\LanguageRepository;
use VitesseCms\Sef\Helpers\SefHelper;
use VitesseCms\Sef\Utils\SefUtil;

class SeoUtil
{
    public static function setSlugsOnItem(
        Item $item,
        DatagroupRepository $datagroupRepository,
        DatafieldRepository $datafieldRepository,
        ItemRepository $itemRepository,
        LanguageRepository $languageRepository,
        Datagroup $datagroup
    ): Item {
        $slugCategories = [];
        $slugDatagroups = array_reverse($datagroup->getSlugCategories());
        /** @var Item $previousItem */
        $previousItem = clone $item;

        foreach ($slugDatagroups as $datagroupArray) :
            if (is_array($datagroupArray) && $previousItem) :
                if ($datagroupArray['published'] && $previousItem->getParentId() !== null):
                    $previousItem = $itemRepository->getById($previousItem->getParentId(), false);
                    if($previousItem !== null) :
                        $slugCategories[] = $previousItem;
                    endif;
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
                    $slugParts[] = SefUtil::generateSlugFromString($slugCategory->_('name', $language->getShortCode()));
                endforeach;

                $slugs[$language->getShortCode()] = implode($datagroup->slugCategoryDelimiter(), $slugParts);
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