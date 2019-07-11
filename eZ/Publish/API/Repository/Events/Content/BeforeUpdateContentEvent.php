<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Content;

use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;

interface BeforeUpdateContentEvent extends BeforeEvent
{
    public function getVersionInfo(): VersionInfo;

    public function getContentUpdateStruct(): ContentUpdateStruct;

    public function getContent(): Content;

    public function setContent(?Content $content): void;

    public function hasContent(): bool;
}
