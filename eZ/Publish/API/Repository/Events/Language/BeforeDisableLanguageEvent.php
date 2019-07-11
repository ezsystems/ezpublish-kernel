<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Language;

use eZ\Publish\API\Repository\Events\BeforeEvent;
use eZ\Publish\API\Repository\Values\Content\Language;

interface BeforeDisableLanguageEvent extends BeforeEvent
{
    public function getLanguage(): Language;

    public function getDisabledLanguage(): Language;

    public function setDisabledLanguage(?Language $disabledLanguage): void;

    public function hasDisabledLanguage(): bool;
}
