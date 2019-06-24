<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Content;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeCreateContentDraftEvent extends BeforeEvent
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    private $contentInfo;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    private $versionInfo;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    private $creator;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Content|null
     */
    private $contentDraft;

    public function __construct(ContentInfo $contentInfo, VersionInfo $versionInfo, User $creator)
    {
        $this->contentInfo = $contentInfo;
        $this->versionInfo = $versionInfo;
        $this->creator = $creator;
    }

    public function getContentInfo(): ContentInfo
    {
        return $this->contentInfo;
    }

    public function getVersionInfo(): VersionInfo
    {
        return $this->versionInfo;
    }

    public function getCreator(): User
    {
        return $this->creator;
    }

    public function getContentDraft(): ?Content
    {
        return $this->contentDraft;
    }

    public function setContentDraft(?Content $contentDraft): void
    {
        $this->contentDraft = $contentDraft;
    }

    public function hasContentDraft(): bool
    {
        return $this->contentDraft instanceof Content;
    }
}
