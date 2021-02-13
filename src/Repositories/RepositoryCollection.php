<?php declare(strict_types=1);

namespace VitesseCms\Content\Repositories;

use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Database\Interfaces\BaseRepositoriesInterface;

class RepositoryCollection
{
    /**
     * @var ItemRepository
     */
    public $item;

    public function __construct(ItemRepository $itemRepository) {
        $this->item = $itemRepository;
    }
}
