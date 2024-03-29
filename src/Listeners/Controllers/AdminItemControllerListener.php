<?php

declare(strict_types=1);

namespace VitesseCms\Content\Listeners\Controllers;

use Phalcon\Events\Event;
use Phalcon\Events\Manager;
use Phalcon\Http\Request;
use VitesseCms\Admin\Forms\AdminlistFormInterface;
use VitesseCms\Content\Controllers\AdminitemController;
use VitesseCms\Content\Models\Item;
use VitesseCms\Content\Repositories\AdminRepositoryCollection;
use VitesseCms\Content\Utils\SeoUtil;
use VitesseCms\Core\Services\CacheService;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Datagroup\Helpers\DatagroupHelper;
use VitesseCms\Datagroup\Models\Datagroup;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Language\Repositories\LanguageRepository;

class AdminItemControllerListener
{
    public function __construct(
        private readonly AdminRepositoryCollection $repositories,
        private readonly LanguageRepository $languageRepository
    ) {
    }

    public function beforeModelSave(Event $event, AdminitemController $controller, Item $item): void
    {
        if (!$item->isDeleted()) :
            $datagroup = $this->repositories->datagroup->getById($item->getDatagroup(), false);
            $item->setIsFilterable($datagroup->hasFilterableFields());
            $item = $this->parseDatafields(
                $item,
                $datagroup,
                $this->repositories->datafield,
                $controller->eventsManager
            );
            $item = $this->setSeoTitle($item);
            if ($item->isHomepage()) {
                $languages = $this->languageRepository->findAll(null, false);
                $slugs = [];
                while ($languages->valid()) :
                    $language = $languages->current();
                    $slugs[$language->getShortCode()] = null;
                    $languages->next();
                endwhile;
                $item->setSlugs($slugs);
            } else {
                $item = SeoUtil::setSlugsOnItem(
                    $item,
                    $this->repositories->datagroup,
                    $this->repositories->datafield,
                    $this->repositories->item,
                    $this->repositories->language,
                    $datagroup
                );
            }

            $this->clearCache($item, $controller->cache);
        endif;
    }

    protected function parseDatafields(
        Item $item,
        Datagroup $datagroup,
        DatafieldRepository $datafieldRepository,
        Manager $eventsManager
    ): Item {
        foreach ($datagroup->getDatafields() as $datafieldArray) :
            $datafield = $datafieldRepository->getById($datafieldArray['id']);
            if ($datafield !== null) :
                $eventsManager->fire($datafield->getType() . ':beforeItemSave', $item, $datafield);
            endif;
        endforeach;

        return $item;
    }

    protected function setSeoTitle(Item $item): Item
    {
        $datagroup = $this->repositories->datagroup->getById($item->getDatagroup(), false);

        $slugCategories = [];
        $seoTitleCategories = array_reverse($datagroup->getSeoTitleCategories());

        $previousItem = clone $item;
        foreach ($seoTitleCategories as $datagroupObject) :
            if (is_array($datagroupObject) && $previousItem) :
                if ($datagroupObject['published'] && $previousItem->getParentId() !== null):
                    $previousItem = $this->repositories->item->getById(
                        $previousItem->getParentId(),
                        false
                    );
                    $slugCategories[] = $previousItem;
                endif;
            endif;
        endforeach;
        /*var_dump($slugCategories);
        die();*/
        $slugDatafields = [];
        foreach ($datagroup->getSeoTitleDatafields() as $datafieldObject) :
            if (is_array($datafieldObject) && $datafieldObject['published']) :
                $slugDatafields[] = $this->repositories->datafield->getById(
                    $datafieldObject['id'],
                    false
                );
            endif;
        endforeach;

        $seoTitle = [];
        $languages = $this->repositories->language->findAll(null, false);
        while ($languages->valid()) :
            $language = $languages->current();
            if (!isset($seoTitle[$language->getShortCode()])) :
                $seoTitle[$language->getShortCode()] = '';
            endif;

            $slugParts = [];
            foreach ($slugDatafields as $datafield) :
                $datafieldResult = $item->_($datafield->getCallingName(), $language->getShortCode());
                if (!empty($datafieldResult)) :
                    if (is_string($datafieldResult)) {
                        if (MongoUtil::isObjectId($datafieldResult)) {
                            $resultItem = $this->repositories->item->getById($datafieldResult);
                            if ($resultItem !== null) :
                                $slugParts[] = $resultItem->getNameField();
                            endif;
                        } else {
                            $slugParts[] = $datafieldResult;
                        }
                    } elseif (is_array($datafieldResult)) {
                        foreach ($datafieldResult as $result) :
                            if (MongoUtil::isObjectId($result)) :
                                $resultItem = $this->repositories->item->getById($result);
                                if ($resultItem !== null) :
                                    $slugParts[] = $resultItem->getNameField();
                                endif;
                            endif;
                        endforeach;
                    }
                endif;
            endforeach;

            if (count($slugCategories) > 0):
                /** @var AbstractCollection $slugCategory */
                foreach ($slugCategories as $slugCategory) :
                    $slugParts[] = $slugCategory->getNameField($language->getShortCode());
                endforeach;
            endif;

            $seoTitle[$language->getShortCode()] = implode(' - ', $slugParts);
            $languages->next();
        endwhile;

        $item->setSeoTitle($seoTitle);

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
            $mainDatagroup = $this->repositories->datagroup->getById(
                $request->get('filter')['datagroup']
            );
            $datagroups = DatagroupHelper::getChildrenFromRoot($mainDatagroup);
            /** @var Datagroup $datagroup */
            foreach ($datagroups as $datagroup) :
                foreach ($datagroup->getDatafields() as $datafield) :
                    if ($datafield['published']) :
                        $datafield = $this->repositories->datafield->getById($datafield['id']);
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
    ): array {
        $items = $this->repositories->item->findAll(
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
        $items = $this->repositories->item->findAll(
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
