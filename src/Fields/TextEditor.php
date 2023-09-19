<?php

declare(strict_types=1);

namespace VitesseCms\Content\Fields;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Datafield\AbstractField;
use VitesseCms\Datafield\Models\Datafield;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Interfaces\AbstractFormInterface;
use VitesseCms\Form\Models\Attributes;

class TextEditor extends AbstractField
{
    public function buildItemFormElement(
        AbstractForm $form,
        Datafield $datafield,
        Attributes $attributes,
        AbstractCollection $data = null
    ) {
        $form->addEditor($datafield->getNameField(), $datafield->getCallingName(), $attributes);
    }

    public function renderFilter(
        AbstractFormInterface $filter,
        Datafield $datafield,
        AbstractCollection $data = null
    ): void {
        $filter->addHidden(
            'filter[textFields][' . uniqid('', false) . ']',
            $datafield->getCallingName()
        );
    }

    public function getSearchValue(
        AbstractCollection $item,
        string $languageShort,
        Datafield $datafield
    ) {
        $result = $item->_($datafield->getCallingName(), $languageShort);
        if (is_string($result)) :
            return trim(strip_tags($result));
        endif;

        return '';
    }
}
