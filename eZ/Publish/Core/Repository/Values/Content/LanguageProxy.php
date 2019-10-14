<?php

declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\Core\Repository\Values\GhostObjectProxyTrait;

final class LanguageProxy extends Language
{
    use GhostObjectProxyTrait;
}
