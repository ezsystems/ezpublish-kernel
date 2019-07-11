<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Content;

use eZ\Publish\API\Repository\Events\Content\UpdateContentEvent as UpdateContentEventInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use Symfony\Contracts\EventDispatcher\Event;

final class UpdateContentEvent extends Event implements UpdateContentEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Content */
    private $content;

    /** @var \eZ\Publish\API\Repository\Values\Content\VersionInfo */
    private $versionInfo;

    /** @var \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct */
    private $contentUpdateStruct;

    public function __construct(
        Content $content,
        VersionInfo $versionInfo,
        ContentUpdateStruct $contentUpdateStruct
    ) {
        $this->content = $content;
        $this->versionInfo = $versionInfo;
        $this->contentUpdateStruct = $contentUpdateStruct;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getVersionInfo(): VersionInfo
    {
        return $this->versionInfo;
    }

    public function getContentUpdateStruct(): ContentUpdateStruct
    {
        return $this->contentUpdateStruct;
    }
}
