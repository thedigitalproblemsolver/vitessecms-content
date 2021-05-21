<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use Phalcon\Events\Event;
use VitesseCms\Block\Forms\BlockForm;
use VitesseCms\Block\Interfaces\RepositoryInterface;
use VitesseCms\Block\Models\Block;
use VitesseCms\Content\Blocks\Filter;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;

class BlockFilterListener
{
    public function buildBlockForm(Event $event, BlockForm $form): void
    {
        $datagroups = $form->di->get('repositories')->datagroup->findAll(new FindValueIterator(
            [new FindValue('component', 'content')]
        ));

        $datagroupIds = [];
        while ($datagroups->valid()) :
            $datagroup = $datagroups->current();
            $datagroupIds[] = (string)$datagroup->getId();
            $datagroups->next();
        endwhile;

        $items = $form->di->get('repositories')->item->findAll(
            new FindValueIterator([new FindValue('datagroup', ['$in' => $datagroupIds])])
        );

        $form->addDropdown(
            '%ADMIN_FILTER_RESULT_TARGET_PAGE%',
            'targetPage',
            (new Attributes())->setOptions(ElementHelper::modelIteratorToOptions($items))
        )->addDropdown(
            '%ADMIN_FILTER_SEARCHABLE_GROUPS%',
            'searchGroups',
            (new Attributes())
                ->setOptions(ElementHelper::modelIteratorToOptions($datagroups))
                ->setMultiple(true)
                ->setInputClass('select2')
        )->addToggle('Use label placeholders', 'labelAsPlaceholder');
    }

    public function loadAssets(Event $event, Filter $filter, Block $block): void
    {
        $block->getDI()->get('assets')->loadFilter();
        $block->getDI()->get('assets')->loadSelect2();
    }
}