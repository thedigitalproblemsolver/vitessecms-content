<?php

declare(strict_types=1);

namespace VitesseCms\Content\Fields;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Datafield\AbstractField;
use VitesseCms\Datafield\Enum\AdminFieldTextInputTypesEnum;
use VitesseCms\Datafield\Models\Datafield;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Interfaces\AbstractFormInterface;
use VitesseCms\Form\Models\Attributes;

final class Text extends AbstractField
{
    public function buildItemFormElement(
        AbstractForm $form,
        Datafield $datafield,
        Attributes $attributes,
        AbstractCollection $data = null
    ) {
        switch ($datafield->getInputType()):
            case AdminFieldTextInputTypesEnum::NUMBER->value:
                $form->addNumber($datafield->getNameField(), $datafield->getCallingName(), $attributes);
                break;
            case AdminFieldTextInputTypesEnum::PHONE->value:
                $form->addPhone($datafield->getNameField(), $datafield->getCallingName(), $attributes);
                break;
            case AdminFieldTextInputTypesEnum::TEXT->value:
                $form->addText($datafield->getNameField(), $datafield->getCallingName(), $attributes);
                break;
            case AdminFieldTextInputTypesEnum::URL->value:
                $form->addUrl($datafield->getNameField(), $datafield->getCallingName(), $attributes);
                break;
            case AdminFieldTextInputTypesEnum::EMAIL->value:
                $form->addEmail($datafield->getNameField(), $datafield->getCallingName(), $attributes);
                break;
            case AdminFieldTextInputTypesEnum::HIDDEN->value:
                $form->addHidden($datafield->getCallingName(), $attributes->getDefaultValue());
                break;
            case AdminFieldTextInputTypesEnum::DATE->value:
                $form->addDate($datafield->getNameField(), $datafield->getCallingName(), $attributes);
                break;
            case AdminFieldTextInputTypesEnum::PASSWORD->value:
                $form->addPassword($datafield->getNameField(), $datafield->getCallingName(), $attributes);
                break;
            default:
                var_dump($datafield->getNameField());
                var_dump($datafield->getInputType());
                die();
        endswitch;
    }

    public function renderFilter(
        AbstractFormInterface $filter,
        Datafield $datafield,
        AbstractCollection $data = null
    ): void {
        $fieldName = 'filter[' . $datafield->getCallingName() . ']';
        switch ($datafield->_('inputType')) :
            case 'number':
                $this->di->assets->loadSlider();
                $fieldName = str_replace('filter[', 'filter[range][', $fieldName);
                $filter->addNumber(
                    $datafield->getNameField(),
                    $fieldName
                /*[
                    'data-slider-id' => 'silder-' . $datafield->_('calling_name'),
                    'data-slider-min' => '0',
                    'data-slider-max' => '40',
                    'data-slider-step' => '1',
                    'data-slider-value' => '[1,40]',
                    'inputClass' => 'slider',
                ]*/
                );
                break;
            default:
                $filter->addHidden(
                    'filter[textFields][' . uniqid('', true) . ']',
                    $datafield->getCallingName()
                );
                break;
        endswitch;
    }

    public function renderAdminlistFilter(AbstractFormInterface $filter, Datafield $datafield): void
    {
        $filter->addText($datafield->getNameField(), $this->getFieldname($datafield));
    }
}
