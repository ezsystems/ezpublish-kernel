<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Content;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class CopyContentEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Content */
    private $content;

    /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo */
    private $contentInfo;

    /** @var \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct */
    private $destinationLocationCreateStruct;

    /** @var \eZ\Publish\API\Repository\Values\Content\VersionInfo */
    private $versionInfo;

    public function __construct(
        Content $content,
        ContentInfo $contentInfo,
        LocationCreateStruct $destinationLocationCreateStruct,
        ?VersionInfo $versionInfo = null
    ) {
        $this->content = $content;
        $this->contentInfo = $contentInfo;
        $this->destinationLocationCreateStruct = $destinationLocationCreateStruct;
        $this->versionInfo = $versionInfo;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getContentInfo(): ContentInfo
    {
        return $this->contentInfo;
    }

    public function getDestinationLocationCreateStruct(): LocationCreateStruct
    {
        return $this->destinationLocationCreateStruct;
    }

    public function getVersionInfo(): ?VersionInfo
    {
        return $this->versionInfo;
    }
}
