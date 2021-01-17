<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use VitesseCms\Content\Helpers\EventVehicleHelper;
use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Core\Services\ViewService;
use VitesseCms\Database\Utils\MongoUtil;

class TagItemListener extends AbstractTagListener
{
    public function __construct()
    {
        $this->name = 'ITEM';
    }

    protected function parse(EventVehicleHelper $contentVehicle, string $tagString): void
    {
        $tagOptions = explode(';', $tagString);
        $replace = '';

        if(!empty($tagOptions[1])) :
            foreach (explode(',',$tagOptions[1]) as $options) :
                if(MongoUtil::isObjectId($options)) :
                    /** @var Item $item */
                    $item = Item::findById($options);
                    if($item instanceof Item) :
                        $replace .= $this->renderItem(
                            $item,
                            $tagOptions[2],
                            $contentVehicle->getView()
                        );
                    endif;
                else :
                    $options = explode(':',$options);
                    if ($options[0] === 'latest') :
                        Item::setFindLimit((int)$options[1]);
                        Item::addFindOrder('createdAt',-1);
                        Item::setFindValue('datagroup',$options[2]);
                        $items = Item::findAll();

                        $cells = 0;
                        if(isset($options[3])) :
                            $cells = (int)$options[3];
                        endif;

                        $cellCounter = 0;
                        if($items) :
                            if(!empty($cells)) :
                                $replace .= '<table width="100%" border="0" cellpadding="5"><tr>';
                            endif;
                            foreach ($items as $item) :
                                if(!empty($cells)) :
                                    if($cellCounter > 1 && is_int($cellCounter/$cells)) :
                                        $replace .= '</tr><tr>';
                                    endif;
                                    $cellCounter++;
                                    $replace .= '<td>';
                                endif;
                                $replace .= $this->renderItem(
                                    $item,
                                    $tagOptions[2],
                                    $contentVehicle->getView()
                                );
                                if(!empty($cells)) :
                                    $replace .= '<br/><br/></td>';
                                endif;
                            endforeach;
                            if(!empty($cells)) :
                                if(!is_int($cellCounter/$cells)) :
                                    $replace .= '</tr>';
                                endif;
                                $replace .= '</table>';
                            endif;
                        endif;
                    endif;
                endif;
            endforeach;
        endif;

        $contentVehicle->set(
            'content',
            str_replace(
                '{'.$this->name.$tagString.'}',
                $replace,
                $contentVehicle->_('content')
            )
        );
    }

    protected function renderItem(Item $item, string $template, ViewService $viewService): string
    {
        ItemHelper::parseBeforeMainContent($item);

        return $viewService->renderTemplate(
            $template,
            'communication/tags/item/',
            ['item' => $item]
        );
    }
}
