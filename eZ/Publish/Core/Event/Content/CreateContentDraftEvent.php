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
use eZ\Publish\Core\Event\AfterEvent;

final class CreateContentDraftEvent extends AfterEvent
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Content
     */
    private $contentDraft;

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

    public function __construct(
        Content $contentDraft,
        ContentInfo $contentInfo,
        ?VersionInfo $versionInfo = null,
        ?User $creator = null
    ) {
        $this->contentDraft = $contentDraft;
        $this->contentInfo = $contentInfo;
        $this->versionInfo = $versionInfo;
        $this->creator = $creator;
    }

    public function getContentDraft(): Content
    {
        return $this->contentDraft;
    }

    public function getContentInfo(): ContentInfo
    {
        return $this->contentInfo;
    }

    public function getVersionInfo(): ?VersionInfo
    {
        return $this->versionInfo;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }
}
