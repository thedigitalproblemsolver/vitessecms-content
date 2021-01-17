<?php declare(strict_types=1);

namespace VitesseCms\Content\Services;

use VitesseCms\Content\Helpers\EventVehicleHelper;
use VitesseCms\Content\Listeners\TagDiscountListener;
use VitesseCms\Content\Listeners\TagItemListener;
use VitesseCms\Content\Listeners\TagOrderSendDateListener;
use VitesseCms\Content\Listeners\TagShopTrackAndTraceListener;
use VitesseCms\Content\Listeners\TagSubscribeListener;
use VitesseCms\Content\Listeners\TagUnsubscribeListener;
use VitesseCms\Core\Services\AbstractInjectableService;
use VitesseCms\Core\Services\ViewService;
use VitesseCms\Sef\Helpers\SefHelper;

class ContentService extends AbstractInjectableService
{
    /**
     * @var array
     */
    protected $eventInputs;

    /**
     * @var ViewService
     */
    protected $view;

    public function __construct(ViewService $viewService)
    {
        $this->view = $viewService;

        if (!$this->eventsManager->hasListeners('contentTag')) :
            $this->eventsManager->attach('contentTag', new TagDiscountListener());
            $this->eventsManager->attach('contentTag', new TagItemListener());
            $this->eventsManager->attach('contentTag', new TagUnsubscribeListener());
            $this->eventsManager->attach('contentTag', new TagShopTrackAndTraceListener());
            $this->eventsManager->attach('contentTag', new TagSubscribeListener());
            $this->eventsManager->attach('contentTag', new TagOrderSendDateListener());
        endif;

        $this->eventInputs = [];
    }

    public function parseListeners(string $content, string $type): string
    {
        $eventVehicle = new EventVehicleHelper($this->view, $this->url);
        $eventVehicle->set('content', $content);
        foreach ($this->eventInputs as $key => $value) :
            $eventVehicle->set($key, $value);
        endforeach;

        $this->eventsManager->fire($type.':apply', $eventVehicle);
        $this->eventInputs = [];

        return $eventVehicle->_('content');
    }

    public function parseContent(
        string $content,
        bool $parseTags = true,
        bool $parseSettings = true
    ): string
    {
        if ($parseTags) :
            $content = $this->parseListeners($content,'contentTag');
        endif;

        $content = $this->language->parsePlaceholders($content);
        $content = SefHelper::parsePlaceholders(
            $content,
            $this->view->getVar('currentId')??''
        );

        /** @todo parse by event */
        if($parseSettings) :
            $content = $this->setting->parsePlaceholders($content);
        endif;

        return $content;
    }

    public function addEventInput(string $key, $value): ContentService
    {
        $this->eventInputs[$key] = $value;

        return $this;
    }

    public function getEventInputs(): array
    {
        return $this->eventInputs;
    }

    public function setEventInputs(array $eventInputs): ContentService
    {
        $this->eventInputs = $eventInputs;

        return $this;
    }
}
