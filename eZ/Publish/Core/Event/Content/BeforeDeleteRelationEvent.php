<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Content;

use eZ\Publish\API\Repository\Events\Content\BeforeDeleteRelationEvent as BeforeDeleteRelationEventInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use Symfony\Contracts\EventDispatcher\Event;

final class BeforeDeleteRelationEvent extends Event implements BeforeDeleteRelationEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\VersionInfo */
    private $sourceVersion;

    /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo */
    private $destinationContent;

    public function __construct(VersionInfo $sourceVersion, ContentInfo $destinationContent)
    {
        $this->sourceVersion = $sourceVersion;
        $this->destinationContent = $destinationContent;
    }

    public function getSourceVersion(): VersionInfo
    {
        return $this->sourceVersion;
    }

    public function getDestinationContent(): ContentInfo
    {
        return $this->destinationContent;
    }
}
