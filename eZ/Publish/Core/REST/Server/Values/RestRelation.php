<?php
/**
 * File containing the RestRelation class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * RestRelation view model
 */
class RestRelation extends RestValue
{
    /**
     * A relation
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Relation
     */
    public $relation;

    /**
     * Content ID to which this relation belongs to
     *
     * @var mixed
     */
    public $contentId;

    /**
     * Version number to which this relation belongs to
     *
     * @var mixed
     */
    public $versionNo;

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Relation $relation
     * @param mixed $contentId
     * @param mixed $versionNo
     */
    public function __construct( Relation $relation, $contentId, $versionNo )
    {
        $this->relation = $relation;
        $this->contentId = $contentId;
        $this->versionNo = $versionNo;
    }
}
