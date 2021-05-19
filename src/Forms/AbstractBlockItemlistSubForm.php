<?php declare(strict_types=1);

namespace VitesseCms\Content\Forms;

use VitesseCms\Block\Forms\BlockForm;
use VitesseCms\Block\Interfaces\RepositoryInterface;
use VitesseCms\Content\Enum\ItemListEnum;
use VitesseCms\Datafield\Models\Datafield;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Datafield\Models\FieldCheckbox;
use VitesseCms\Datafield\Models\FieldDatagroup;
use VitesseCms\Datafield\Models\FieldPrice;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;

abstract class AbstractBlockItemlistSubForm
{
    protected static function buildDatafieldValueForm(BlockForm $form, string $datagroupId, RepositoryInterface $repositories): void
    {
        $datagroup = $repositories->datagroup->getById($datagroupId);
        if ($datagroup !== null) :
            $form->addHtml('<h2>' . $datagroup->_('name') . '</h2>');
            foreach ($datagroup->getDatafields() as $datafieldOptions) :
                /** @var Datafield $datafield */
                $datafield = $repositories->datafield->getById($datafieldOptions['id']);
                if ($datafield !== null) :
                    $fieldName = 'datafieldValue[' . $datafield->getCallingName() . ']';
                    $name = $datafield->getNameField();
                    switch ($datafield->getFieldType()):
                        case 'FieldCheckbox':
                        case FieldCheckbox::class:
                            $form->addDropdown(
                                $name,
                                $fieldName,
                                (new Attributes())->setOptions(ElementHelper::arrayToSelectOptions([
                                    'both' => 'selected or not selected',
                                    'selected' => 'selected',
                                    'notSelected' => 'not selected',
                                ])
                                ));
                            break;
                        case 'FieldPrice':
                        case FieldPrice::class:
                            $form->addDropdown(
                                $name . ' discount',
                                'datafieldValue[discount]',
                                (new Attributes())->setOptions(ElementHelper::arrayToSelectOptions([
                                    'bothEmpty' => 'empty or not empty',
                                    'empty' => 'empty',
                                    'notEmpty' => 'not empty',
                                ])
                                ));
                            break;
                        case 'FieldDatagroup':
                        case FieldDatagroup::class:
                            $options = [];
                            if ($datafield->getDatagroup() !== null) {
                                $items = $repositories->item->findAll(new FindValueIterator(
                                    [new FindValue('datagroup', $datafield->getDatagroup())]
                                ));
                                $options = ElementHelper::modelIteratorToOptions($items);
                                $options[] = [
                                    'value' => ItemListEnum::OPTION_CURRENT_ITEM,
                                    'label' => '%FORM_OPTION_ACTIVE_ITEM%',
                                    'selected' => null,
                                ];
                            }
                            $form->addDropdown(
                                $name,
                                $fieldName,
                                (new Attributes())->setOptions($options)
                            );
                            break;
                        default:
                            $form->addText($name, $fieldName);
                            break;
                    endswitch;
                endif;
            endforeach;
        endif;
    }
}
