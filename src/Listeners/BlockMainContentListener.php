<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use Phalcon\Events\Event;
use VitesseCms\Block\Forms\BlockForm;

class BlockMainContentListener
{
    public function buildBlockForm(Event $event, BlockForm $form): void
    {
        $form->addToggle('Use datagroup template', 'useDatagroupTemplate');
    }
}