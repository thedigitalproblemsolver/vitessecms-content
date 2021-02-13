<?php declare(strict_types=1);

namespace VitesseCms\Content\Repositories;

use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Database\Interfaces\BaseRepositoriesInterface;

class AdminRepositoryCollection implements BaseRepositoriesInterface
{
    /**
     * @var ItemRepository
     */
    public $item;

    /**
     * @var DatagroupRepository
     */
    public $datagroup;

    public function __construct(
        ItemRepository $itemRepository,
        DatagroupRepository $datagroupRepository
    ) {
        $this->item = $itemRepository;
        $this->datagroup = $datagroupRepository;
    }
}
