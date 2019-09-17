<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\DraftList\Item;

use eZ\Publish\API\Repository\Values\Content\DraftList\ContentDraftListItemInterface;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;

/**
 * Item of content drafts list.
 */
class ContentDraftListItem implements ContentDraftListItemInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    private $versionInfo;

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     */
    public function __construct(VersionInfo $versionInfo)
    {
        $this->versionInfo = $versionInfo;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo|null
     */
    public function getVersionInfo(): ?VersionInfo
    {
        return $this->versionInfo;
    }

    /**
     * @return bool
     */
    public function hasVersionInfo(): bool
    {
        return $this->versionInfo instanceof VersionInfo;
    }
}
