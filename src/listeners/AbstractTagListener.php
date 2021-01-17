<?php

namespace VitesseCms\Content\Listeners;

use VitesseCms\Content\Helpers\EventVehicleHelper;
use Phalcon\Events\Event;

/**
 * Class DiscountListener
 * @package Modules\Shop\Events
 */

abstract class AbstractTagListener
{
    protected $name;

    /**
     * @param Event $event
     * @param EventVehicleHelper $contentVehicle
     */
    public function apply(Event $event, EventVehicleHelper $contentVehicle): void
    {
        if($this->hasTag($contentVehicle->_('content'))) :
            $tagsFromBody = $this->getTagsFromBody($contentVehicle->_('content'));
            if(\is_array($tagsFromBody) && isset($tagsFromBody[1]) && \is_array($tagsFromBody[1])) :
                foreach ($tagsFromBody[1] as $tagString) :
                    $this->parse($contentVehicle, $tagString);
                endforeach;
            endif;
        endif;
    }

    /**
     * @param string $content
     *
     * @return array
     */
    protected function getTagsFromBody(string $content): array
    {
        preg_match_all('/{'.$this->name.'(.*?)\}/', $content, $tagsFromBody);

        return $tagsFromBody;
    }

    /**
     * @param string $content
     *
     * @return bool
     */
    protected function hasTag(string $content): bool
    {
        return (bool)substr_count($content, '{'.$this->name);
    }

    /**
     * @param EventVehicleHelper $contentVehicle
     * @param string $tagString
     */
    abstract protected function parse(EventVehicleHelper $contentVehicle, string $tagString):void;
}
