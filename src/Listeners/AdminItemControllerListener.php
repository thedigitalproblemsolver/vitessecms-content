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
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
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
            $item = $this->setSeoTitle($item, $controller);
            $item = $this->setSlugs($item, $controller);
            $this->clearCache($item, $controller->cache);
        endif;
    }

    protected function setSeoTitle(Item $item, AdminitemController $controller): Item
    {
        $datagroup = $controller->repositories->datagroup->getById($item->getDatagroup(), false);

        $slugCategories = [];
        $seoTitleCategories = array_reverse($datagroup->getSeoTitleCategories());

        $previousItem = clone $item;
        foreach ($seoTitleCategories as $datagroupObject) :
            if (is_array($datagroupObject) && $previousItem) :
                if ($datagroupObject['published'] && $previousItem->getParentId() !== null):
                    $slugCategories[] = $controller->repositories->item->getById(
                        $previousItem->getParentId(),
                        false
                    );
                endif;
            endif;
        endforeach;

        $slugDatafields = [];
        foreach ($datagroup->getSeoTitleDatafields() as $datafieldObject) :
            if (is_array($datafieldObject) && $datafieldObject['published']) :
                $slugDatafields[] = $controller->repositories->datafield->getById(
                    $datafieldObject['id'],
                    false
                );
            endif;
        endforeach;

        $seoTitle = [];
        $languages = $controller->repositories->language->findAll(null, false);
        while ($languages->valid()) :
            $language = $languages->current();
            if (!isset($seoTitle[$language->getShortCode()])) :
                $seoTitle[$language->getShortCode()] = '';
            endif;

            $slugParts = [];
            foreach ($slugDatafields as $datafield) :
                $datafieldResult = $item->_($datafield->getCallingName(), $language->getShortCode());
                if (!empty($datafieldResult)) :
                    if (is_string($datafieldResult)) :
                        $slugParts[] = $datafieldResult;
                    elseif (is_array($datafieldResult)) :
                        foreach ($datafieldResult as $result) :
                            if (MongoUtil::isObjectId($result)) :
                                $resultItem = $controller->repositories->item->getById($result);
                                if ($resultItem !== null) :
                                    $slugParts[] = $resultItem->getNameField();
                                endif;
                            endif;
                        endforeach;
                    endif;
                endif;
            endforeach;

            if (count($slugCategories) > 0):
                /** @var AbstractCollection $slugCategory */
                foreach ($slugCategories as $slugCategory) :
                    $slugParts[] = $slugCategory->getNameField($language->getShortCode());
                endforeach;
            endif;

            $seoTitle[$language->getShortCode()] = implode(' ', $slugParts);
            $languages->next();
        endwhile;

        $item->setSeoTitle($seoTitle);

        return $item;
    }

    protected function setSlugs(Item $item, AdminitemController $controller): Item
    {
        $datagroupRepository = $controller->repositories->datagroup;
        $datafieldRepository = $controller->repositories->datafield;
        $itemRepository = $controller->repositories->item;
        $languageRepository = $controller->repositories->language;

        $datagroup = $datagroupRepository->getById($item->getDatagroup(), false);
        $item->setIsFilterable($datagroup->hasFilterableFields());
        foreach ($datagroup->getDatafields() as $datafieldArray) :
            $datafield = $datafieldRepository->getById($datafieldArray['id']);
            if (is_object($datafield)) :
                $controller->eventsManager->fire($datafield->getFieldType() . ':beforeSave', $item, $datafield);
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

    public function adminListFilter(Event $event, AdminitemController $controller, AdminlistFormInterface $form): string
    {
        $form->addHidden('filter[datagroup]');

        $request = new Request();
        if (isset($request->get('filter')['datagroup'])) :
            $mainDatagroup = $controller->repositories->datagroup->getById(
                $request->get('filter')['datagroup']
            );
            $datagroups = DatagroupHelper::getChildrenFromRoot($mainDatagroup);
            /** @var Datagroup $datagroup */
            foreach ($datagroups as $datagroup) :
                foreach ($datagroup->getDatafields() as $datafield) :
                    if ($datafield['published']) :
                        $datafield = $controller->repositories->datafield->getById($datafield['id']);
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
                    ElementHelper::arrayToSelectOptions(
                        $this->getParentOptionsFromDatagroup(
                            $mainDatagroup,
                            $controller
                        )
                    )
                )
            );

        endif;

        $form->addPublishedField($form);

        return $form->renderForm(
            $controller->getLink() . '/' . $controller->router->getActionName(),
            'adminFilter'
        );
    }

    protected function getParentOptionsFromDatagroup(
        Datagroup $datagroup,
        AdminitemController $controller,
        array $parentOptions = []
    ): array
    {
        $items = $controller->repositories->item->findAll(
            new FindValueIterator([new FindValue('datagroup', (string)$datagroup->getId())])
        );
        foreach ($items as $item) :
            if ($item->hasChildren()):
                $parentOptions[(string)$item->getId()] = $item->_('name');
                $this->getParentOptionsFromItem(
                    $item,
                    $controller,
                    $parentOptions
                );
            endif;
        endforeach;

        return $parentOptions;
    }

    protected function getParentOptionsFromItem(
        AbstractCollection $parent,
        AdminitemController $controller,
        array &$parentOptions = [],
        array $prefix = []
    ): void {
        $items = $controller->repositories->item->findAll(
            new FindValueIterator([new FindValue('parentId', (string)$parent->getId())])
        );
        $prefix[] = $parent->_('name');
        foreach ($items as $item) :
            if ($item->hasChildren()):
                $parentOptions[(string)$item->getId()] = implode(' > ', $prefix) . ' > ' . $item->_('name');
                $this->getParentOptionsFromItem(
                    $item,
                    $controller,
                    $parentOptions,
                    $prefix
                );
            endif;
        endforeach;
    }
}
