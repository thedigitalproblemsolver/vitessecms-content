<?php

declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use Phalcon\Events\Event;
use Phalcon\Events\Manager;
use VitesseCms\Core\DTO\BeforeExecuteFrontendRouteDTO;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;

class FrontendListener
{
    public function __construct(
        private readonly Manager $eventsManager,
        private readonly DatagroupRepository $datagroupRepository,
        private readonly DatafieldRepository $datafieldRepository
    ) {
    }

    public function beforeExecuteRoute(Event $event, BeforeExecuteFrontendRouteDTO $beforeExecuteRouteDTO): void
    {
        if ($beforeExecuteRouteDTO->currentItem !== null) {
            $datagroup = $this->datagroupRepository->getById($beforeExecuteRouteDTO->currentItem->getDatagroup());
            if ($datagroup !== null) {
                foreach ($datagroup->getDatafields() as $datafieldData) {
                    if ($datafieldData['published']) {
                        $datafield = $this->datafieldRepository->getById($datafieldData['id']);
                        if ($datafield !== null) {
                            $beforeExecuteRouteDTO->datafield = $datafield;
                            $this->eventsManager->fire(
                                $datafield->getType() . ':beforeExecuteFrontendRoute',
                                $beforeExecuteRouteDTO
                            );
                        }
                    }
                }
            }
        }
    }
}