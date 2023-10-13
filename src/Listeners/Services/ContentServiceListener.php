<?php
declare(strict_types=1);

namespace VitesseCms\Content\Listeners\Services;

use Phalcon\Events\Event;
use Phalcon\Events\Manager;
use VitesseCms\Content\Services\ContentService;
use VitesseCms\Core\Services\UrlService;
use VitesseCms\Core\Services\ViewService;
use VitesseCms\Language\Services\LanguageService;
use VitesseCms\Setting\Services\SettingService;

class ContentServiceListener
{
    /**
     * @var ViewService
     */
    private $view;

    /**
     * @var UrlService
     */
    private $url;

    /**
     * @var Manager
     */
    private $eventsManager;

    /**
     * @var LanguageService
     */
    private $language;

    private $setting;

    public function __construct(
        ViewService $viewService,
        UrlService $urlService,
        Manager $eventsManager,
        LanguageService $languageService,
        SettingService $settingService
    ) {
        $this->view = $viewService;
        $this->url = $urlService;
        $this->eventsManager = $eventsManager;
        $this->language = $languageService;
        $this->setting = $settingService;
    }

    public function attach(Event $event): ContentService
    {
        return new ContentService($this->view, $this->url, $this->eventsManager, $this->language, $this->setting);
    }
}
