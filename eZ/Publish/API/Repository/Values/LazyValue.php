<?php

declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values;

interface LazyValue
{
    public function getValue();
}
