<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\Section
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content\Criterion;

/**
 * @package ezp.persistence.content.criteria
 */
class Section extends Criterion
{
    /**
     * Creates a new Section criterion
     *
     * @param integer|array(integer) $sectionId
     * @throws InvalidArgumentException if a non numeric id is given
     */
    public function __construct( $sectionId )
    {
        if ( !is_array( $sectionId ) )
        {
            $sectionId = array( $sectionId );
        }
        foreach( $sectionId as $id )
        {
            if ( !is_numeric( $id ) )
            {
                throw new \InvalidArgumentException( "Only numeric section ids are accepted" );
            }
        }
        $this->operator = Operator::IN;
    }

    /**
     * List of ContentSection ids to match against
     * @var array(integer)
     */
    public $sectionIdList;
}
?>
