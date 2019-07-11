<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Content;

use eZ\Publish\API\Repository\Events\Content\BeforeCreateContentDraftEvent as BeforeCreateContentDraftEventInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\User\User;
use Symfony\Contracts\EventDispatcher\Event;
use UnexpectedValueException;

final class BeforeCreateContentDraftEvent extends Event implements BeforeCreateContentDraftEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo */
    private $contentInfo;

    /** @var \eZ\Publish\API\Repository\Values\Content\VersionInfo */
    private $versionInfo;

    /** @var \eZ\Publish\API\Repository\Values\User\User */
    private $creator;

    /** @var \eZ\Publish\API\Repository\Values\Content\Content|null */
    private $contentDraft;

    public function __construct(ContentInfo $contentInfo, ?VersionInfo $versionInfo = null, ?User $creator = null)
    {
        $this->contentInfo = $contentInfo;
        $this->versionInfo = $versionInfo;
        $this->creator = $creator;
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

    public function getContentDraft(): Content
    {
        if (!$this->hasContentDraft()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasContentDraft() or set it by setContentDraft() before you call getter.', Content::class));
        }

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
