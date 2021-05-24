<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners\Tags;

use VitesseCms\Content\Helpers\EventVehicleHelper;
use Phalcon\Events\Event;

abstract class AbstractTagListener
{
    protected $name;

    public function apply(Event $event, EventVehicleHelper $contentVehicle): void
    {
        if ($this->hasTag($contentVehicle->_('content'))) :
            $tagsFromBody = $this->getTagsFromBody($contentVehicle->_('content'));
            if (is_array($tagsFromBody) && isset($tagsFromBody[1]) && is_array($tagsFromBody[1])) :
                foreach ($tagsFromBody[1] as $tagString) :
                    $this->parse($contentVehicle, $tagString);
                endforeach;
            endif;
        endif;
    }

    protected function hasTag(string $content): bool
    {
        return (bool)substr_count($content, '{' . $this->name);
    }

    protected function getTagsFromBody(string $content): array
    {
        preg_match_all('/{' . $this->name . '(.*?)\}/', $content, $tagsFromBody);

        return $tagsFromBody;
    }

    abstract protected function parse(EventVehicleHelper $contentVehicle, string $tagString): void;
}
