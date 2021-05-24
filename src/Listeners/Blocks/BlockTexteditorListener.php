<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use Phalcon\Events\Event;
use VitesseCms\Block\Forms\BlockForm;
use VitesseCms\Form\Models\Attributes;

class BlockTexteditorListener
{
    public function buildBlockForm(Event $event, BlockForm $form): void
    {
        $form->addEditor('%ADMIN_TEXT%', 'text', (new Attributes())->setMultilang());
    }
}