<?php declare(strict_types=1);

namespace VitesseCms\Content\Controllers;

use VitesseCms\Core\AbstractController;
use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Helpers\ItemHelper;

class IndexController extends AbstractController
{
    public function indexAction(): void
    {
        $this->prepareView();
    }

    public function searchAction(): void
    {
        $result = ['items' => []];

        if ($this->request->isAjax() && strlen($this->request->get('search')) > 1) :
            Item::setFindValue(
                'name.'.$this->configuration->getLanguageShort(),
                $this->request->get('search'),
                'like'
            );
            $items = Item::findAll();

            if($items) :
                foreach ($items as $item ) :
                    /** @var Item $item */
                    $path = ItemHelper::getPathFromRoot($item);
                    $tmp = [
                        'id' => (string)$item->getId(),
                        'name' => implode(' - ',$path),
                    ];
                    $result['items'][] = $tmp;
                endforeach;
            endif;
        endif;

        $this->prepareJson($result);
    }

    public function setGeoCoordinatesAction(): void
    {
        if(
            $this->request->isAjax()
            && !empty($this->request->getPost('latitude'))
            && !empty($this->request->getPost('longitude'))
        ) {
            $item = Item::findById($this->request->getPost('id'));
            if($item):
                $item->set('latitude',$this->request->getPost('latitude'))
                    ->set('longitude',$this->request->getPost('longitude'))
                    ->save()
                ;
            endif;
        }

        $this->disableView();
    }
}
