<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Language;

use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;

interface BeforeCreateLanguageEvent
{
    public function getLanguageCreateStruct(): LanguageCreateStruct;

    public function getLanguage(): Language;

    public function setLanguage(?Language $language): void;

    public function hasLanguage(): bool;
}
