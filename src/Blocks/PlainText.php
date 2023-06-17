<?php declare(strict_types=1);

namespace VitesseCms\Content\Blocks;

use VitesseCms\Block\AbstractBlockModel;
use VitesseCms\Block\Models\Block;

class PlainText extends AbstractBlockModel
{
    public function getTemplateParams(Block $block): array
    {
        $params = parent::getTemplateParams($block);
        $params['text'] = $block->_('text');
        $params['title'] = $block->getNameField();

        return $params;
    }
}
