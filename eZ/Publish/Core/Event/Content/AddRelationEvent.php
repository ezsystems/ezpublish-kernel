<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Content;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Event\AfterEvent;

final class AddRelationEvent extends AfterEvent
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Relation
     */
    private $relation;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    private $sourceVersion;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    private $destinationContent;

    public function __construct(
        Relation $relation,
        VersionInfo $sourceVersion,
        ContentInfo $destinationContent
    ) {
        $this->relation = $relation;
        $this->sourceVersion = $sourceVersion;
        $this->destinationContent = $destinationContent;
    }

    public function getRelation(): Relation
    {
        return $this->relation;
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
