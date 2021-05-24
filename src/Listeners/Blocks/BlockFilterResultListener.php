<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use Phalcon\Events\Event;
use VitesseCms\Block\Forms\BlockForm;
use VitesseCms\Form\Models\Attributes;

class BlockFilterResultListener
{
    public function buildBlockForm(Event $event, BlockForm $form): void
    {
        $form->addText('%ADMIN_HEADING%', 'heading', (new Attributes())->setRequired()->setMultilang())
            ->addEditor('%ADMIN_INTROTEXT%', 'introtext', (new Attributes())->setRequired()->setMultilang())
            ->addEditor('%ADMIN_FILTER_NO_RESULT_TEXT%', 'noresultText', (new Attributes())->setRequired()->setMultilang())
        ;
    }
}