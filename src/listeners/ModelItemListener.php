<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use VitesseCms\Content\Models\Item;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Core\Models\Datafield;
use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Core\Repositories\DatafieldRepository;
use VitesseCms\Core\Repositories\DatagroupRepository;
use VitesseCms\Core\Services\CacheService;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\Field\AbstractField;
use VitesseCms\Language\Models\Language;
use VitesseCms\Language\Repositories\LanguageRepository;
use VitesseCms\Sef\Helpers\SefHelper;
use Phalcon\Events\Event;
use Phalcon\Utils\Slug;

class ModelItemListener
{
    public function beforeModelSave(
        Event $event,
        Item $item,
        InjectableInterface $container
    ): void
    {
        if (!$item->isDeleted()) :
            $item = $this->setSeoTitle($item);
            $item = $this->setSlugs($item);
            $this->clearCache($item, $container->cache);
        endif;
    }

    protected function setSlugs(Item $item): Item
    {
        $datagroupRepository = new DatagroupRepository();
        $datafieldRepository = new DatafieldRepository();
        $itemRepository = new ItemRepository();
        $languageRepository = new LanguageRepository();

        $datagroup = $datagroupRepository->getById($item->getDatagroup(), false);
        $item->setIsFilterable($datagroup->hasFilterableFields());
        foreach ($datagroup->getDatafields() as $datafieldArray) :
            $datafield = $datafieldRepository->getById($datafieldArray['id']);
            if (is_object($datafield)) :
                $static = $datafield->getClass();
                /** @var AbstractField $static */
                $static::beforeSave($item, $datafield);
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

    protected function setSeoTitle(Item $item): Item
    {
        Datagroup::setFindPublished(false);
        /** @var Datagroup $datagroup */
        $datagroup = Datagroup::findById($item->getDatagroup());

        $slugCategories = [];
        $slugDatagroups = array_reverse($datagroup->getSeoTitleCategories());
        /** @var Item $previousItem */
        $previousItem = clone $item;
        foreach ($slugDatagroups as $datagroupObject) :
            if (is_array($datagroupObject) && $previousItem) :
                Item::setFindPublished(false);
                if ($datagroupObject['published'] && $previousItem->getParentId() !== null):
                    $previousItem = Item::findById($previousItem->getParentId());
                    $slugCategories[] = $previousItem;
                endif;
            endif;
        endforeach;

        $slugDatafields = [];
        foreach ($datagroup->getSeoTitleDatafields() as $datafieldObject) :
            if (is_array($datafieldObject) && $datafieldObject['published']) :
                $slugDatafields[] = Datafield::findById($datafieldObject['id']);
            endif;
        endforeach;

        $seoTitle = [];
        Language::setFindPublished(false);
        $languages = Language::findAll();
        /** @var Language $language */
        foreach ($languages as $language) :
            if (!isset($seoTitle[$language->getShortCode()])) :
                $seoTitle[$language->getShortCode()] = '';
            endif;

            $slugParts = [];
            /** @var Datafield $datafield */
            foreach ($slugDatafields as $datafield) :
                $datafieldResult = $item->_($datafield->getCallingName(), $language->getShortCode());
                if (!empty($datafieldResult)) :
                    if (is_string($datafieldResult)) :
                        $slugParts[] = $datafieldResult;
                    elseif (is_array($datafieldResult)) :
                        foreach ($datafieldResult as $result) :
                            if (MongoUtil::isObjectId($result)) :
                                $resultItem = Item::findById($result);
                                if ($resultItem !== null) :
                                    $slugParts[] = $resultItem->_('name');
                                endif;
                            endif;
                        endforeach;
                    endif;
                endif;
            endforeach;

            if (count($slugCategories) > 0):
                /** @var AbstractCollection $slugCategory */
                foreach ($slugCategories as $slugCategory) :
                    $slugParts[] = $slugCategory->_('name', $language->getShortCode());
                endforeach;
            endif;

            $seoTitle[$language->getShortCode()] = implode(' ', $slugParts);
        endforeach;

        $item->setSeoTitle($seoTitle);

        return $item;
    }

    protected function clearCache(Item $item, CacheService $cache): void
    {
        foreach ($item->getSlugs() as $key => $slug) :
            $cache->delete($cache->getCacheKey($key . $slug));
        endforeach;
    }
}
