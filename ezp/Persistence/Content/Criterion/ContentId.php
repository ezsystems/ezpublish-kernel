<?php
/**
 * File containing the \ezp\Persistence\Content\Criterion\ContentId class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
<<<<<<< HEAD:ezp/Persistence/Content/Criterion/ContentId.php
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content\Criterion;

/**
 * @package ezp.Persistence.Content.Criterion
 */
class ContentId extends Criterion
{
    /**
     * Creates a new ContentId criterion
     *
     * The criterion will be matched if at least one of the given Ids are matched
     *
     * @param integer|array(integer) One or more content Id that must be matched
     *
     * @throw InvalidArgumentException if a non numeric id is given
     */
    public function __construct( $contentId )
    {
        if ( !is_array( $contentId ) )
        {
            $contentId = array( $contentId );
        }
        foreach( $contentId as $id )
        {
            if ( !is_numeric( $id ) )
            {
                throw new \InvalidArgumentException( "Only numeric content ids are accepted" );
            }
        }
        $this->contentIdList = $contentId;
    }

    /**
     * A list of contentId that must be matched
     * @var array
     */
    public $contentIdList;
}
?>
