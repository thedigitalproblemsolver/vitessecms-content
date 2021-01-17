<?php declare(strict_types=1);

namespace VitesseCms\Content\Models;

class ItemIterator extends \ArrayIterator
{
    public function __construct(array $products)
    {
        parent::__construct($products);
    }

    public function current(): Item
    {
        return parent::current();
    }

    public function add(Item $value): void
    {
        $this->append($value);
    }
}
