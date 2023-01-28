<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use VitesseCms\Content\Repositories\ItemRepository;

class ItemListener
{
    private ItemRepository $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    public function getRepository(): ItemRepository
    {
        return $this->itemRepository;
    }
}
