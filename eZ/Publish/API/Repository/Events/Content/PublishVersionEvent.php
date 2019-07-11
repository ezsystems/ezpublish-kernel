<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Content;

use eZ\Publish\API\Repository\Events\AfterEvent;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;

interface PublishVersionEvent extends AfterEvent
{
    public function getContent(): Content;

    public function getVersionInfo(): VersionInfo;

    public function getTranslations(): array;
}