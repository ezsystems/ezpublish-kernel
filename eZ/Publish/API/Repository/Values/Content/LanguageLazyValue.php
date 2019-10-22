<?php

declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content;

interface LanguageLazyValue
{
    public function getValue(): Language;
}
