<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Language;

use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use eZ\Publish\API\Repository\Values\Content\Language;

interface BeforeUpdateLanguageNameEvent extends BeforeEvent
{
    public function getLanguage(): Language;

    public function getNewName(): string;

    public function getUpdatedLanguage(): Language;

    public function setUpdatedLanguage(?Language $updatedLanguage): void;

    public function hasUpdatedLanguage(): bool;
}
