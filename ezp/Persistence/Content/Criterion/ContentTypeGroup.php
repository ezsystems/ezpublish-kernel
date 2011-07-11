<?php
/**
 * File containing the \ezp\Persistence\Content\Criterion\ContentTypeGroup
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Criterion;

/**
 * @package ezp.persistence.content.criteria
 */
class ContentTypeGroup extends Criterion
{
    /**
     * Creates a new ContentTypeGroup criterion
     *
     * Content will be matched if it matches one of the $contentTypeGroupId
     *
     * @param integer|array(integer) one or more (as an array) ContentTypeGroup id
     *
     * @throws InvalidArgumentException if a non numeric id is given
     */
    public function __construct( $contentTypeGroupId )
    {
        if ( !is_array( $contentTypeGroupId ) )
        {
            $contentTypeGroupId = array( $contentTypeGroupId );
        }
        foreach( $contentTypeGroupId as $id )
        {
            if ( !is_numeric( $contentTypeGroupId ) )
            {
                throw new \InvalidArgumentException( "Only numeric content type group ids are accepted" );
            }
        }
        $this->contentTypeGroupIdList = $contentTypeGroupId;
    }

    /**
     * List of ContentTypeGroup ids that must be matched
     * @var array(integer)
     */
    public $contentTypeGroupIdList;
}
?>



