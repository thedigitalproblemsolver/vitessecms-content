<?php declare(strict_types=1);

namespace VitesseCms\Content\Factories;

use ChrisKonnertz\OpenGraph\OpenGraph;
use VitesseCms\Content\Models\Item;
use VitesseCms\Configuration\Services\ConfigService;
use VitesseCms\Setting\Services\SettingService;

class OpengraphFactory
{
    public static function createFormItem(
        Item $item,
        SettingService $setting,
        ConfigService $config
    ): OpenGraph {
        try {
            $opengraph = new OpenGraph();
            $opengraph->title($item->_('name'));
            $opengraph->type('article');

            if ($item->_('price_sale')) :
                $opengraph->type('product');
                $opengraph->attributes(
                    'product',
                    [
                        'price:amount'   => $item->_('price_sale'),
                        'price:currency' => $setting->get('SHOP_CURRENCY_ISO'),
                        'availability'   => 'instock',
                    ]
                );
            endif;

            if ($item->_('image')) :
                $opengraph->image(
                    $config->getUploadUri().$item->_('image')
                );
            endif;

            if ($setting->has('SEO_META_DESCRIPTION')) :
                $opengraph->description($setting->get('SEO_META_DESCRIPTION'));
            endif;

            if (trim($item->_('introtext'))) :
                $opengraph->description($item->_('introtext'));
            endif;

            $opengraph->url($item->_('slug'));

            return $opengraph;
        } catch (\Exception $exception) {
            echo $exception->getMessage();
            die();
        }
    }
}
