<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Content;

use eZ\Publish\API\Repository\Events\BeforeEvent;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\User\User;

interface BeforeCreateContentDraftEvent extends BeforeEvent
{
    public function getContentInfo(): ContentInfo;

    public function getVersionInfo(): ?VersionInfo;

    public function getCreator(): ?User;

    public function getContentDraft(): Content;

    public function setContentDraft(?Content $contentDraft): void;

    public function hasContentDraft(): bool;
}
