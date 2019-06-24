<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Content;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforePublishVersionEvent extends BeforeEvent
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    private $versionInfo;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Content|null
     */
    private $content;

    public function __construct(VersionInfo $versionInfo)
    {
        $this->versionInfo = $versionInfo;
    }

    public function getVersionInfo(): VersionInfo
    {
        return $this->versionInfo;
    }

    public function getContent(): ?Content
    {
        return $this->content;
    }

    public function setContent(?Content $content): void
    {
        $this->content = $content;
    }

    public function hasContent(): bool
    {
        return $this->content instanceof Content;
    }
}
