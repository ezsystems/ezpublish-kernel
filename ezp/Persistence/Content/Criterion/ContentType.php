<?php
/**
 * File containing the \ezp\Persistence\Content\Criterion\ContentType class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content\Criterion;

/**
 * @package ezp.Persistence.Content.Criterion
 */
class ContentType extends Criterion
{
    /**
     * Creates a new ContentType criterion
     *
     * Content will be matched if it matches one of the $contentTypeId
     *
     * @param integer|array(integer) one or more (as an array) ContentType id
     *
     * @throws InvalidArgumentException if a non numeric id is given
     */
    public function __construct( $contentTypeId )
    {
        if ( !is_array( $contentTypeId ) )
        {
            $contentTypeId = array( $contentTypeId );
        }
        foreach( $contentTypeId as $id )
        {
            if ( !is_numeric( $contentTypeId ) )
            {
                throw new \InvalidArgumentException( "Only numeric content type ids are accepted" );
            }
        }
        $this->contentTypeIdList = $contentTypeId;
    }

    /**
     * List of ContentType ids that must be matched
     * @var array(integer)
     */
    public $contentTypeIdList;
}

?>
