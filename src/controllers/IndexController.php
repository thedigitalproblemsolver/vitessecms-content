<?php declare(strict_types=1);

namespace VitesseCms\Content\Controllers;

use VitesseCms\Content\Repositories\RepositoriesInterface;
use VitesseCms\Core\AbstractController;
use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;

class IndexController extends AbstractController implements RepositoriesInterface
{
    public function indexAction(): void
    {
        $this->prepareView();
    }

    public function searchAction(): void
    {
        $result = ['items' => []];

        if ($this->request->isAjax() && strlen($this->request->get('search')) > 1) :
            $items = $this->repositories->item->findAll(new FindValueIterator(
                [new FindValue(
                    'name.'.$this->configuration->getLanguageShort(),
                    $this->request->get('search'),
                    'like'
                )]
            ));

            if($items->count() > 0) :
                while ($items->valid()) :
                    $item = $items->current();
                    $path = ItemHelper::getPathFromRoot($item);
                    $tmp = [
                        'id' => (string)$item->getId(),
                        'name' => implode(' - ',$path),
                    ];
                    $result['items'][] = $tmp;
                    $item = $items->next();
                endwhile;
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
            $item = $this->repositories->item->getById($this->request->getPost('id'));
            if($item !== null):
                $item->set('latitude',$this->request->getPost('latitude'))
                    ->set('longitude',$this->request->getPost('longitude'))
                    ->save()
                ;
            endif;
        }

        $this->disableView();
    }
}
