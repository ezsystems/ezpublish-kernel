<?php

declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content;

/**
 * @internal
 */
interface SectionLazyValue
{
    public function getValue(): Section;
}
