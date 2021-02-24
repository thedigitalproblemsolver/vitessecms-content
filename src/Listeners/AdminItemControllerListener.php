<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use Phalcon\Events\Event;
use Phalcon\Http\Request;
use Phalcon\Utils\Slug;
use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Admin\Forms\AdminlistFormInterface;
use VitesseCms\Content\Controllers\AdminitemController;
use VitesseCms\Content\Models\Item;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\Interfaces\BaseObjectInterface;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Core\Services\CacheService;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\Datafield\Models\Datafield;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Datagroup\Helpers\DatagroupHelper;
use VitesseCms\Datagroup\Models\Datagroup;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Interfaces\AbstractFormInterface;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Language\Models\Language;
use VitesseCms\Language\Repositories\LanguageRepository;
use VitesseCms\Sef\Helpers\SefHelper;

class AdminItemControllerListener
{
    public function beforeModelSave(Event $event, AdminitemController $controller, Item $item): void
    {
        if (!$item->isDeleted()) :
            $item = $this->setSeoTitle($item);
            $item = $this->setSlugs($item);
            $this->clearCache($item, $controller->cache);
        endif;
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

    protected function clearCache(Item $item, CacheService $cache): void
    {
        foreach ($item->getSlugs() as $key => $slug) :
            $cache->delete($cache->getCacheKey($key . $slug));
        endforeach;
    }

    public function adminListFilter(Event $event, AbstractAdminController $controller, AdminlistFormInterface $form): string
    {
        $form->addHidden('filter[datagroup]');

        $request = new Request();
        if (isset($request->get('filter')['datagroup'])) :
            $mainDatagroup = Datagroup::findById($request->get('filter')['datagroup']);
            $datagroups = DatagroupHelper::getChildrenFromRoot($mainDatagroup);
            /** @var Datagroup $datagroup */
            foreach ($datagroups as $datagroup) :
                foreach ($datagroup->getDatafields() as $datafield) :
                    if ($datafield['published']) :
                        $datafield = Datafield::findById($datafield['id']);
                        if ($datafield && $datafield->isPublished()) :
                            $datafield->renderAdminlistFilter($form);
                        endif;
                    endif;
                endforeach;
            endforeach;

            $form->addDropdown(
                'Has as parent',
                'filter[parentId]',
                (new Attributes())->setOptions(
                    ElementHelper::arrayToSelectOptions($this->getParentOptionsFromDatagroup($mainDatagroup))
                )
            );

        endif;

        $form->addPublishedField($form);

        return $form->renderForm(
            $controller->getLink() . '/' . $controller->router->getActionName(),
            'adminFilter'
        );
    }

    protected function getParentOptionsFromDatagroup(Datagroup $datagroup, array $parentOptions = []): array
    {
        Item::setFindValue('datagroup', (string)$datagroup->getId());
        $items = Item::findAll();
        foreach ($items as $item) :
            if ($item->hasChildren()):
                $parentOptions[(string)$item->getId()] = $item->_('name');
                $this->getParentOptionsFromItem($item, $parentOptions);
            endif;
        endforeach;

        return $parentOptions;
    }

    protected function getParentOptionsFromItem(
        AbstractCollection $parent,
        array &$parentOptions = [],
        array $prefix = []
    ): void
    {
        Item::setFindValue('parentId', (string)$parent->getId());
        $prefix[] = $parent->_('name');
        $items = Item::findAll();
        foreach ($items as $item) :
            if ($item->hasChildren()):
                $parentOptions[(string)$item->getId()] = implode(' > ', $prefix) . ' > ' . $item->_('name');
                $this->getParentOptionsFromItem($item, $parentOptions, $prefix);
            endif;
        endforeach;
    }
}
