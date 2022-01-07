<?php declare(strict_types=1);

namespace VitesseCms\Content\Helpers;

use VitesseCms\Core\Interfaces\BaseObjectInterface;
use VitesseCms\Core\Services\UrlService;
use VitesseCms\Core\Services\ViewService;
use VitesseCms\Core\Traits\BaseObjectTrait;

class EventVehicleHelper implements EventVehicleInterface, BaseObjectInterface
{
    use BaseObjectTrait;

    /**
     * @var ViewService
     */
    private $view;

    /**
     * @var UrlService
     */
    private $url;

    /**\
     * @var string
     */
    private $content;

    public function __construct(
        ViewService $viewService,
        UrlService $urlService,
        string $content
    )
    {
        $this->view = $viewService;
        $this->url = $urlService;
        $this->content  = $content;
    }

    public function getView(): ViewService
    {
        return $this->view;
    }

    public function getUrl(): UrlService
    {
        return $this->url;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
