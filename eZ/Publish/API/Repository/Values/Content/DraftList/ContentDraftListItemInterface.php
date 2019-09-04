<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\DraftList;

use eZ\Publish\API\Repository\Values\Content\VersionInfo;

interface ContentDraftListItemInterface
{
    /**
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo|null
     */
    public function getVersionInfo(): ?VersionInfo;

    /**
     * @return bool
     */
    public function hasVersionInfo(): bool;
}
