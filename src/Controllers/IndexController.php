<?php

declare(strict_types=1);

namespace VitesseCms\Content\Controllers;

use stdClass;
use VitesseCms\Content\Enum\ItemEnum;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\AbstractControllerFrontend;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;

class IndexController extends AbstractControllerFrontend
{
    private ItemRepository $itemRepository;

    public function OnConstruct()
    {
        parent::onConstruct();

        $this->itemRepository = $this->eventsManager->fire(ItemEnum::GET_REPOSITORY, new stdClass());
    }

    public function indexAction(): void
    {
    }

    public function searchAction(): void
    {
        $result = ['items' => []];

        if ($this->request->isAjax() && $this->request->has('search')) {
            $items = $this->itemRepository->findAll(
                new FindValueIterator(
                    [
                        new FindValue(
                            'name.' . $this->configService->getLanguageShort(),
                            $this->request->get('search'),
                            'like'
                        )
                    ]
                )
            );

            if ($items->count() > 0) {
                while ($items->valid()) {
                    $item = $items->current();
                    $path = ItemHelper::getPathFromRoot($item);
                    $tmp = [
                        'id' => (string)$item->getId(),
                        'name' => implode(' - ', $path),
                    ];
                    $result['items'][] = $tmp;
                    $items->next();
                }
            }
        }

        $this->jsonResponse($result);
    }
}
