<?php

declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\Core\Repository\Values\GhostObjectProxyTrait;

class SectionProxy extends Section
{
    use GhostObjectProxyTrait;

    protected function initialize(): void
    {
        $data = $this->initializer->send($this->id);
        foreach ($data as $property => $value) {
            $this->$property = $value;
        }

        $this->initializer->next();
        $this->initializer = null;
    }
}
