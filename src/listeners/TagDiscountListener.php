<?php declare(strict_types=1);

namespace VitesseCms\Content\Listeners;

use VitesseCms\Communication\Models\NewsletterQueue;
use VitesseCms\Content\Helpers\EventVehicleHelper;
use VitesseCms\Core\Interfaces\BaseObjectInterface;
use VitesseCms\Core\Services\ViewService;
use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\Shop\Enum\DiscountEnum;
use VitesseCms\Shop\Factories\DiscountFactory;
use VitesseCms\Shop\Models\Discount;
use \DateTime;

class TagDiscountListener extends AbstractTagListener
{
    public function __construct()
    {
        $this->name = 'DISCOUNT';
    }

    protected function parse(EventVehicleHelper $contentVehicle, string $tagString): void
    {
        $tagOptions = explode(';', $tagString);
        $replace = '';
        if (isset($tagOptions[1]) && MongoUtil::isObjectId($tagOptions[1])) :
            /** @var Discount $item */
            $discount = Discount::findById($tagOptions[1]);
            if ($discount instanceof Discount) :
                $replace .= $this->renderDiscountTag($discount, $tagOptions[2], $contentVehicle->getView());
            endif;
        elseif (isset($tagOptions[1])) :
            $options = explode(':', $tagOptions[1]);
            switch ($options[0]) :
                case 'personalOnOrder':
                    $replace .= $this->renderDiscountTag(
                        $this->getPersonalOnOrder($contentVehicle, $options),
                        $tagOptions[2],
                        $contentVehicle->getView()
                    );
                    break;
                case 'latestByPrefix':
                    if (!empty($options[4]) && $contentVehicle->_('newsletterQueueId')) :
                        $newsletterQueue = NewsletterQueue::findById($contentVehicle->_('newsletterQueueId'));
                        if ($newsletterQueue) :
                            Discount::setFindValue('code', $options[4], 'like');
                            Discount::setFindValue('name.nl', $newsletterQueue->_('email'), 'like');
                            $discount = Discount::findFirst();
                            if ($discount instanceof Discount) :
                                $replace .= $this->renderDiscountTag($discount, $tagOptions[2], $contentVehicle->getView());
                            else :
                                $replace .= $this->renderDiscountTag(
                                    $this->getPersonalOnOrder($contentVehicle, $options),
                                    $tagOptions[2],
                                    $contentVehicle->getView()
                                );
                            endif;
                        endif;
                    else :
                        $replace .= $this->renderDiscountTag(
                            $this->getPersonalOnOrder($contentVehicle, $options),
                            $tagOptions[2],
                            $contentVehicle->getView()
                        );
                    endif;
                    break;
            endswitch;
        endif;

        $contentVehicle->set(
            'content',
            str_replace(
                '{' . $this->name . $tagString . '}',
                $replace,
                $contentVehicle->_('content')
            )
        );
    }

    protected function getPersonalOnOrder(BaseObjectInterface $contentVehicle, array $options): Discount
    {
        $email = '';
        if ($contentVehicle->_('newsletterQueueId')) :
            $newsletterQueue = NewsletterQueue::findById($contentVehicle->_('newsletterQueueId'));
            if ($newsletterQueue) :
                $email = ' - ' . $newsletterQueue->_('email');
            endif;
        endif;

        $discount = $this->createRandomDiscount(DiscountEnum::TARGET_ORDER, $options);
        $discount->set('published', true);
        $discount->set('type', DiscountEnum::TYPE_CURRENCY);
        $discount->set(
            'name',
            $discount->_('name') . ' - ' . $discount->_('code') . ' - ' . $discount->_('amount') . ' - ' . DiscountEnum::TYPE_CURRENCY . $email,
            true
        );
        if ($contentVehicle->_('newsletterQueueId')) :
            $discount->save();
        endif;

        return $discount;
    }

    protected function renderDiscountTag(Discount $discount, string $template, ViewService $viewService): string
    {
        return $viewService->renderTemplate(
            $template,
            'communication/tags/discount/',
            ['discount' => $discount]
        );
    }

    protected function createRandomDiscount(string $target, ?array $options = null): Discount
    {
        $amount = 0;
        $tillDate = $fromDate = null;
        $prefix = 'P';

        if (!empty($options[1])) :
            $tillDate = new DateTime($options[1]);
        endif;

        if (!empty($options[2])) :
            $amount = (int) $options[2];
        endif;

        if (!empty($options[3])) :
            $fromDate = new DateTime($options[3]);
        endif;

        if (!empty($options[4])) :
            $prefix = $options[4];
        endif;

        return DiscountFactory::createRandom(
            'Personalized - ' . $target,
            $target,
            $amount,
            $prefix,
            $tillDate,
            $fromDate
        );
    }
}
