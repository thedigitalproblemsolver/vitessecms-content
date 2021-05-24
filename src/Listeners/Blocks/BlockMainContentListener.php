<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners\Blocks;

use Phalcon\Events\Event;
use VitesseCms\Block\Forms\BlockForm;
use VitesseCms\Block\Models\Block;
use VitesseCms\Content\Blocks\MainContent;
use VitesseCms\Datagroup\Models\Datagroup;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Form\Models\Attributes;

class BlockMainContentListener
{
    /**
     * @var DatagroupRepository
     */
    private $datagroupRepository;

    public function __construct(DatagroupRepository $datagroupRepository)
    {
        $this->datagroupRepository = $datagroupRepository;
    }

    public function buildBlockForm(Event $event, BlockForm $form): void
    {
        $form->addToggle('Use datagroup template', 'useDatagroupTemplate')
            ->addNumber('Overview item limit', 'overviewItemLimit', (new Attributes())->setRequired())
        ;
    }

    public function parse(Event $event, MainContent $mainContent, Block $block): void
    {
        $this->handleAddressTemplate($mainContent, $block);
    }

    public function loadAssets(Event $event, MainContent $mainContent): void
    {
        if ($mainContent->getDi()->view->hasCurrentItem()) :
            $item = $mainContent->getDi()->view->getCurrentItem();
            /** @var Datagroup $datagroup */
            $datagroup = $this->datagroupRepository->getById($item->getDatagroup());
            if (substr_count($datagroup->getTemplate(), 'address')) :
                $mainContent->getDi()->assets->loadGoogleMaps(
                    $mainContent->getDi()->setting->get('GOOGLE_MAPS_APIKEY')
                );
            endif;

            if (substr_count($datagroup->getTemplate(), 'shop_clothing_design_overview')) :
                $mainContent->getDi()->assets->loadLazyLoading();
            endif;
        endif;
    }

    private function handleAddressTemplate(MainContent $mainContent, Block $block):void
    {
        if (substr_count($mainContent->getTemplate(), 'address')) :
            $markerFile = $mainContent-getDi()->configuration->getUploadDir() . 'google-maps-icon-marker.png';
            $markerUrl = $mainContent->getDi()->configuration->getUploadUri() . '/google-maps-icon-marker.png';
            if (is_file($markerFile)) :
                $block->set('googleMapsMarkerIcon', $markerUrl);
            endif;
        endif;
    }
}