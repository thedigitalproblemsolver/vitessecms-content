<?php declare(strict_types=1);

namespace VitesseCms\Content\Models;

use Phalcon\Incubator\MongoDB\Mvc\CollectionInterface;
use Phalcon\Mvc\View;
use VitesseCms\Core\Services\ViewService;
use VitesseCms\Database\AbstractCollection;
use Phalcon\Di\Di;
use VitesseCms\Datafield\Models\Datafield;

class Item extends AbstractCollection
{

    /**
     * @var bool
     */
    protected static $renderFields = true;
    /**
     * @var array
     */
    public $slug;
    /**
     * @var bool
     */
    public $homepage;
    /**
     * @var string
     */
    public $datagroup;
    /**
     * @var string
     */
    public $parentId;
    /**
     * @var array
     */
    public $seoTitle;
    /**
     * @var int
     */
    public $ordering;
    /**
     * @var bool
     */
    protected $isFilterable = false;

    /**
     * @deprecated move to repositrory or helper
     */
    public static function findById($id): ?CollectionInterface
    {
        $item = parent::findById($id);
        $viewService = new ViewService(
            Di::getDefault()->get('configuration')->getCoreTemplateDir(),
            Di::getDefault()->get('configuration')->getVendorNameDir(),
            new View()
        );
        if (self::$renderFields && $item) :
            $dataFieldTemplates = (new Datafield())->getTemplates();
            foreach ($dataFieldTemplates as $path => $name) :
                $item->set($name, $viewService->renderTemplate(
                    'core',
                    $path,
                    ['item', $item]
                ));
            endforeach;
        endif;

        self::setRenderFields(true);

        return $item;
    }

    /**
     * @param bool $renderFields
     */
    public static function setRenderFields(bool $renderFields)
    {
        self::$renderFields = $renderFields;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->_('name');
    }

    /**
     * @param string $name
     * @param string|null $languageShort
     *
     * @return mixed
     */
    public function getSearchValue(string $name, string $languageShort = null)
    {
        return $this->_($name, $languageShort);
    }

    public function getSlugs(): array
    {
        return $this->slug ?? [];
    }

    public function setSlugs(array $slugs): Item
    {
        $this->slug = $slugs;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug[Di::getDefault()->get('configuration')->getLanguageShort()] ?? '';
    }

    public function isHomepage(): bool
    {
        return (bool)$this->homepage;
    }

    public function getDatagroup(): ?string
    {
        return $this->datagroup;
    }

    public function setDatagroup(string $datagroup): Item
    {
        $this->datagroup = $datagroup;

        return $this;
    }

    public function setIsFilterable(bool $isFilterable): Item
    {
        $this->isFilterable = $isFilterable;

        return $this;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function getOrdering(): int
    {
        return (int)$this->ordering;
    }

    public function setOrdering($ordering): Item
    {
        $this->ordering = (int)$ordering;

        return $this;
    }

    public function setSeoTitle(array $seoTitle): Item
    {
        $this->seoTitle = $seoTitle;

        return $this;
    }
}
