<?php declare(strict_types=1);

namespace VitesseCms\Content\Helpers;

use VitesseCms\Content\Interfaces\EventVehicleInterface;
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
    protected $view;

    /**
     * @var UrlService
     */
    protected $url;

    public function __construct(ViewService $viewService, UrlService $urlService)
    {
        $this->view = $viewService;
        $this->url = $urlService;
    }

    public function getView(): ViewService
    {
        return $this->view;
    }

    public function getUrl(): UrlService
    {
        return $this->url;
    }
}
