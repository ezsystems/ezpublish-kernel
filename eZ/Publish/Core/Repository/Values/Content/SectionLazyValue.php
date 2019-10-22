<?php

declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\SectionLazyValue as SectionLazyValueInterface;
use Generator;

final class SectionLazyValue implements SectionLazyValueInterface
{
    /** @var int */
    private $id;

    /** @var \Generator */
    private $generator;

    /** @var \eZ\Publish\API\Repository\Values\Content\Section|null */
    private $value;

    public function __construct(int $id, Generator $generator)
    {
        $this->id = $id;
        $this->generator = $generator;
    }

    public function getValue(): Section
    {
        if ($this->value === null) {
            $this->initialize();
        }

        return $this->value;
    }

    private function initialize(): void
    {
        $this->generator->send($this->id);
        $this->generator->next();
    }
}
