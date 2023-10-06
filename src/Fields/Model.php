<?php

declare(strict_types=1);

namespace VitesseCms\Content\Fields;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Datafield\AbstractField;
use VitesseCms\Datafield\Models\Datafield;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;

class Model extends AbstractField
{
    public function buildItemFormElement(
        AbstractForm $form,
        Datafield $datafield,
        Attributes $attributes,
        AbstractCollection $data = null
    ) {
        $model = $datafield->getModel();
        $model::addFindOrder('name');
        if ($datafield->_('displayLimit') && $datafield->_('displayLimit') > 0):
            $model::setFindLimit($datafield->getInt('displayLimit'));
        endif;


        if (isset($datafield->datagroups) && is_array($datafield->datagroups)):
            $model::setFindValue('datagroup', ['$in' => $datafield->datagroups]);
        endif;

        $attributes->setOptions(ElementHelper::arrayToSelectOptions($model::findAll()));

        if ($datafield->_('useSelect2')) :
            $attributes->setInputClass('select2');
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
