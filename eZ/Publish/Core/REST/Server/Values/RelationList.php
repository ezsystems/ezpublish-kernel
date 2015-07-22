<?php

/**
 * File containing the RelationList class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * Relation list view model.
 */
class RelationList extends RestValue
{
    /**
     * Relations.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Relation[]
     */
    public $relations;

    /**
     * Content ID to which this relation belongs to.
     *
     * @var mixed
     */
    public $contentId;

    /**
     * Version number to which this relation belongs to.
     *
     * @var mixed
     */
    public $versionNo;

    /**
     * Path used to load the list of relations.
     *
     * @var string
     */
    public $path;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Relation[] $relations
     * @param mixed $contentId
     * @param mixed $versionNo
     * @param string $path
     */
    public function __construct(array $relations, $contentId, $versionNo, $path = null)
    {
        $this->relations = $relations;
        $this->contentId = $contentId;
        $this->versionNo = $versionNo;
        $this->path = $path;
    }
}
