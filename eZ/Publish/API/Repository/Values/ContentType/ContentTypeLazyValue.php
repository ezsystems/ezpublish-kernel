<?php

declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\ContentType;

interface ContentTypeLazyValue
{
    public function getValue(): ContentType;
}
