<?php declare(strict_types=1);

namespace VitesseCms\Content\Blocks;

use VitesseCms\Block\AbstractBlockModel;
use VitesseCms\Block\Models\Block;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Admin\Utils\AdminUtil;

class Breadcrumbs extends AbstractBlockModel
{
    public function parse(Block $block): void
    {
        parent::parse($block);

        if (
            !AdminUtil::isAdminPage()
            && !$this->di->shop->checkout->isCurrentItemCheckout()
            && $this->view->hasCurrentItem()
        ) {
            $block->set('items', ItemHelper::getPathFromRoot($this->view->getCurrentItem()));
            $block->set('hasItems', true);
        }
    }
}
