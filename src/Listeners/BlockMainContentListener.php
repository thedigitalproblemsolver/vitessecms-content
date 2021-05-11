<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use Phalcon\Events\Event;
use VitesseCms\Block\Forms\BlockForm;
use VitesseCms\Form\Models\Attributes;

class BlockMainContentListener
{
    public function buildBlockForm(Event $event, BlockForm $form): void
    {
        $form->addToggle('Use datagroup template', 'useDatagroupTemplate')
            ->addNumber('Overview item limit', 'overviewItemLimit', (new Attributes())->setRequired())
        ;
    }
}