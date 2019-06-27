<?php

/**
 * File containing the Relation class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Relation as APIRelation;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Values\Content\Relation}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\Content\Relation
 */
class Relation extends APIRelation
{
    /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo */
    protected $sourceContentInfo;

    /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo */
    protected $destinationContentInfo;

    /** @var int */
    protected $type;

    /**
     * the content of the source content of the relation.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function getSourceContentInfo()
    {
        return $this->sourceContentInfo;
    }

    /**
     * the content of the destination content of the relation.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function getDestinationContentInfo()
    {
        return $this->destinationContentInfo;
    }
}
