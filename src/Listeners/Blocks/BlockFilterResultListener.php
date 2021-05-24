<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners\Blocks;

use Phalcon\Events\Event;
use VitesseCms\Block\Forms\BlockForm;
use VitesseCms\Form\Models\Attributes;

class BlockFilterResultListener
{
    public function buildBlockForm(Event $event, BlockForm $form): void
    {
        $attributes = (new Attributes())->setRequired()->setMultilang();
        $form->addText('%ADMIN_HEADING%', 'heading', $attributes)
            ->addEditor('%ADMIN_INTROTEXT%', 'introtext', $attributes)
            ->addEditor('%ADMIN_FILTER_NO_RESULT_TEXT%', 'noresultText', $attributes)
        ;
    }
}