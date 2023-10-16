<?php

declare(strict_types=1);

namespace VitesseCms\Content\Listeners\Blocks;

use Phalcon\Events\Event;
use VitesseCms\Block\Forms\BlockForm;
use VitesseCms\Content\Blocks\MainContent;
use VitesseCms\Content\Models\Item;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Form\Models\Attributes;

final class BlockMainContentListener
{
    public function __construct(
        private readonly DatagroupRepository $datagroupRepository,
        private readonly ?Item $currentItem
    ) {
    }

    public function buildBlockForm(Event $event, BlockForm $form): void
    {
        $form->addToggle('Use datagroup template', 'useDatagroupTemplate')
            ->addNumber('Overview item limit', 'overviewItemLimit', (new Attributes())->setRequired());
    }

    public function loadAssets(Event $event, MainContent $mainContent): void
    {
        if ($this->currentItem !== null) {
            $datagroup = $this->datagroupRepository->getById($this->currentItem->getDatagroup());
            if (substr_count($datagroup->getTemplate(), 'shop_clothing_design_overview')) {
                $mainContent->getDi()->assets->loadLazyLoading();
            }
        }
    }
}