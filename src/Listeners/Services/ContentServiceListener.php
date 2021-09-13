<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners\Services;

use VitesseCms\Content\Services\ContentService;
use VitesseCms\Core\Services\ViewService;

class ContentServiceListener
{
    /**
     * @var ViewService
     */
    private $view;

    public function __construct(ViewService $view)
    {
        $this->view = $view;
    }

    public function attach( Event $event): ContentService
    {
        return new ContentService($this->view);
    }
}
