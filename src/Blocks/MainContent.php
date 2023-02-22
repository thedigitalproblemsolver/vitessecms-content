<?php declare(strict_types=1);

namespace VitesseCms\Content\Blocks;

use VitesseCms\Block\AbstractBlockModel;
use VitesseCms\Block\Models\Block;

class MainContent extends AbstractBlockModel
{
    public function parse(Block $block): void
    {
        parent::parse($block);
        if ($this->view->hasCurrentItem()) :
            $item = $this->view->getCurrentItem();
            if($block->getBool('useDatagroupTemplate')) :
                $datagroup = $this->di->get('repositories')->datagroup->getById($item->getDatagroup());
                if ($datagroup->getTemplate() !== null) :
                    $this->template = $datagroup->getTemplate();
                endif;
            endif;
            $block->set('imageFullWidth', true);
            $this->di->get('eventsManager')->fire(get_class($this) . ':parse', $this, $block);
        endif;
    }

    public function getCacheKey(Block $block): string
    {
        return parent::getCacheKey($block) . $this->view->getCurrentItem()->getUpdatedOn()->getTimestamp();
    }

    public function getTemplateParams(Block $block): array
    {
        $params = parent::getTemplateParams($block);
        $params['pagination'] = $block->_('pagination');

        return $params;
    }
}
