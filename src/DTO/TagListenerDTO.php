<?php

declare(strict_types=1);

namespace VitesseCms\Content\DTO;

class TagListenerDTO
{
    protected string $tagString;

    final public function __construct(string $tagString)
    {
        $this->tagString = $tagString;
    }

    public function getTagString(): string
    {
        return $this->tagString;
    }
}