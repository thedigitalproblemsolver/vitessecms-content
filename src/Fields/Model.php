<?php declare(strict_types=1);

namespace VitesseCms\Content\Fields;

use VitesseCms\Datafield\Models\Datafield;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Datafield\AbstractField;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Media\Enums\AssetsEnum;

class Model extends AbstractField
{
    public function buildItemFormElement(AbstractForm $form, Datafield $datafield, Attributes $attributes, AbstractCollection $data = null)
    {
        $model = $datafield->getModel();
        $model::addFindOrder('name');
        if ($datafield->_('displayLimit') && $datafield->_('displayLimit') > 0):
            $model::setFindLimit($datafield->_('displayLimit'));
        endif;

        if (!empty($datafield->getDatagroup())):
            $model::setFindValue('datagroup', ['$in' => [$datafield->getDatagroup()]]);
        endif;

        $attributes->setOptions(ElementHelper::arrayToSelectOptions($model::findAll()));

        if ($datafield->_('useSelect2')) :
            $attributes->setInputClass(AssetsEnum::SELECT2);
        endif;

        if ($datafield->_('multiple')) :
            $attributes->setMultiple();
        endif;

        $form->addDropdown($datafield->getNameField(), $datafield->getCallingName(), $attributes);
    }

    public function renderSlugPart(AbstractCollection $item, string $languageShort, Datafield $datafield): string
    {
        return 'page:' . $item->_($datafield->getCallingName());
    }
}
