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
use eZ\Publish\Core\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeAddRelationEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\Content\VersionInfo */
    private $sourceVersion;

    /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo */
    private $destinationContent;

    /** @var \eZ\Publish\API\Repository\Values\Content\Relation|null */
    private $relation;

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

    public function getRelation(): Relation
    {
        if (!$this->hasRelation()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasRelation() or set it by setRelation() before you call getter.', Relation::class));
        }

        return $this->relation;
    }

    public function setRelation(?Relation $relation): void
    {
        $this->relation = $relation;
    }

    public function hasRelation(): bool
    {
        return $this->relation instanceof Relation;
    }
}
