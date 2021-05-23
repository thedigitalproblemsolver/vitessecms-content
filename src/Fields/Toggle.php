<?php declare(strict_types=1);

namespace VitesseCms\Content\Fields;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Datafield\Models\Datafield;
use VitesseCms\Datafield\AbstractField;
use VitesseCms\Form\Interfaces\AbstractFormInterface;
use VitesseCms\Form\Models\Attributes;

class Toggle extends AbstractField
{
    public function buildItemFormElement(
        AbstractForm $form,
        Datafield $datafield,
        Attributes $attributes,
        AbstractCollection $data = null
    )
    {
        if (
            is_object($data)
            && $data->_($datafield->getCallingName())
        ) :
            $attributes->setChecked();
        endif;

        $form->addToggle($datafield->getNameField(), $datafield->getCallingName(), $attributes);
    }

    public function renderFilter(AbstractFormInterface $filter, Datafield $datafield): void
    {
        $filter->addToggle($datafield->getNameField(), $this->getFieldname($datafield));
    }
}
