<?php
/**
 * File containing the RelationList class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

/**
 * Relation list view model
 */
class RelationList
{
    /**
     * ID of the content object the relations belong to
     *
     * @var mixed
     */
    public $contentId;

    /**
     * Relations
     *
     * @var array
     */
    public $relations;

    /**
     * Construct
     *
     * @param mixed $contentId
     * @param array $relations
     */
    public function __construct( $contentId, array $relations )
    {
        $this->contentId = $contentId;
        $this->relations = $relations;
    }
}
