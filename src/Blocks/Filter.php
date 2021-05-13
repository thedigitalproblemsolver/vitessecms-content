<?php declare(strict_types=1);

namespace VitesseCms\Content\Blocks;

use VitesseCms\Block\AbstractBlockModel;
use VitesseCms\Block\Models\Block;
use VitesseCms\Block\Models\BlockPosition;
use VitesseCms\Content\Models\Item;
use VitesseCms\Datafield\Models\Datafield;
use VitesseCms\Datagroup\Models\Datagroup;
use VitesseCms\Form\Forms\BaseForm;
use VitesseCms\Media\Enums\AssetsEnum;
use MongoDB\BSON\ObjectID;
use function is_object;

class Filter extends AbstractBlockModel
{
    public function parse(Block $block): void
    {
        parent::parse($block);

        $ids = [];
        $filter = new BaseForm();
        $filter->setLabelAsPlaceholder((bool)$block->_('labelAsPlaceholder'));

        $templateParts = explode('/', $block->_('template'));
        $templateParts = array_reverse($templateParts);
        $templatePath = $this->di->config->get('defaultTemplateDir') .
            'views/blocks/Filter/' .
            ucfirst($templateParts[0]
            )
        ;

        $filter->addHtml($this->view->renderTemplate('_filter_form', $templatePath));
        $filter->addHtml($this->view->renderTemplate('_filter_container_start', $templatePath));

        if (substr_count(strtolower($templateParts[0]), 'horizontal')) :
            $filter->setFormTemplate('form_horizontal');
        endif;

        foreach ((array)$block->_('searchGroups') as $searchGroupId) :
            $datagroup = Datagroup::findById($searchGroupId);
            foreach ((array)$datagroup->_('datafields') as $field) :
                if (!empty($field['filterable'])) :
                    $datafield = Datafield::findById($field['id']);
                    /** @var Datafield $datafield */
                    if (is_object($datafield) && $datafield->_('published')) :
                        $datafield->renderFilter($filter);
                    endif;
                endif;
            endforeach;
        endforeach;

        $filter->addHtml($this->view->renderTemplate('_filter_container_end', $templatePath));
        $filter->addHidden('searchGroups', implode(',', $ids));
        $filter->addHidden('firstRun', 'true');

        BlockPosition::setFindValue('datagroup', ['$in' => ['page:' . $block->_('targetPage')]]);
        $resultBlockPosition = BlockPosition::findFirst();

        Block::setFindValue('_id', new ObjectID($resultBlockPosition->_('block')));
        $resultBlock = Block::findFirst();
        $filter->addHidden('blockId', (string)$resultBlock->getId());

        /** @var Item $item */
        $item = Item::findById($block->_('targetPage'));
        $block->set('filter', $filter->renderForm($item->getSlug(), 'filter'));
    }

    public function loadAssets(Block $block): void
    {
        parent::loadAssets($block);

        $this->di->assets->load(AssetsEnum::FILTER);
        $this->di->assets->load(AssetsEnum::SELECT2);
    }
}
