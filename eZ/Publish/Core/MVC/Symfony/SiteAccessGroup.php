<?php

declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony;

final class SiteAccessGroup
{
    /** @var string */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
