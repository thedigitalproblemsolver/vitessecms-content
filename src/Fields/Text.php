<?php declare(strict_types=1);

namespace VitesseCms\Content\Fields;

use VitesseCms\Datafield\Models\Datafield;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Datafield\AbstractField;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Interfaces\AbstractFormInterface;
use VitesseCms\Form\Models\Attributes;

class Text extends AbstractField
{
    public function buildItemFormElement(
        AbstractForm $form,
        Datafield $datafield,
        Attributes $attributes,
        AbstractCollection $data = null
    )
    {
        switch ($datafield->getInputType()):
            case 'number':
                $form->addNumber($datafield->getNameField(), $datafield->getCallingName(), $attributes);
                break;
            case 'tel':
                $form->addPhone($datafield->getNameField(), $datafield->getCallingName(), $attributes);
                break;
            case 'text':
                $form->addText($datafield->getNameField(), $datafield->getCallingName(), $attributes);
                break;
            case 'url':
                $form->addUrl($datafield->getNameField(), $datafield->getCallingName(), $attributes);
                break;
            case 'email':
                $form->addEmail($datafield->getNameField(), $datafield->getCallingName(), $attributes);
                break;
            case 'hidden':
                $form->addHidden($datafield->getCallingName());
                break;
            default:
                var_dump($datafield->getInputType());
                die();
        endswitch;
    }

    public function renderFilter(
        AbstractFormInterface $filter,
        Datafield $datafield,
        AbstractCollection $data = null
    ): void
    {
        $fieldName = 'filter[' . $datafield->getCallingName() . ']';
        switch ($datafield->_('inputType')) :
            case 'number':
                $this->di->assets->loadSlider();
                $fieldName = str_replace('filter[', 'filter[range][', $fieldName);
                $filter->_(
                    'text',
                    $datafield->getNameField(),
                    $fieldName,
                    [
                        'data-slider-id' => 'silder-' . $datafield->_('calling_name'),
                        'data-slider-min' => '0',
                        'data-slider-max' => '40',
                        'data-slider-step' => '1',
                        'data-slider-value' => '[1,40]',
                        'inputClass' => 'slider',
                    ]
                );
                break;
            default:
                $filter->_(
                    'hidden',
                    null,
                    'filter[textFields][' . uniqid('', true) . ']',
                    [
                        'value' => $datafield->getCallingName(),
                    ]
                );
                break;
        endswitch;
    }

    public function renderAdminlistFilter(AbstractFormInterface $filter, Datafield $datafield): void
    {
        $filter->addText($datafield->getNameField(), $this->getFieldname($datafield));
    }
}
