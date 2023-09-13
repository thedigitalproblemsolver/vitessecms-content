<?php
declare(strict_types=1);

namespace VitesseCms\Content\Listeners\Fields;

use DateTime;
use MongoDB\BSON\UTCDateTime;
use Phalcon\Events\Event;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Datafield\Enum\AdminFieldTextInputTypesEnum;
use VitesseCms\Datafield\Models\Datafield;

class TextListener
{
    public function beforeItemSave(Event $event, AbstractCollection $item, Datafield $datafield): void
    {
        switch ($datafield->getInputType()) {
            case AdminFieldTextInputTypesEnum::DATE->value:
                $date = DateTime::createFromFormat('Y-m-d', $item->_($datafield->getCallingName()));
                if ($date) {
                    $item->set($datafield->getCallingName(), new UTCDateTime($date));
                }
                break;
        }
    }
}