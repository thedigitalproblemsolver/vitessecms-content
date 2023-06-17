<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners\Blocks;

use Phalcon\Events\Event;
use VitesseCms\Block\Forms\BlockForm;
use VitesseCms\Form\Models\Attributes;

class BlockPlainTextListener
{
    public function buildBlockForm(Event $event, BlockForm $form): void
    {
        $form->addText('%ADMIN_TEXT%', 'text', (new Attributes())->setMultilang());
    }
}