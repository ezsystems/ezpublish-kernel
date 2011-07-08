<?php
/**
 * File containing Criterion class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage content/criteria
 */
namespace ezp\Content\Criteria;

/**
 * Criterion base class
 * A criterion is a logical association between 2 or more criterias
 */
abstract class Criterion
{
    /**
     * Criterias contained in current criterion
     * @var array( Criteria )
     */
    protected $criteria = array();

    /**
     * Constructor
     * @param array( Criteria ) $criterias
     * @throws \InvalidArgumentException If at least one of passed criterias is not a valid Criteria object
     */
    public function __construct( array $criteria )
    {
        foreach ( $criterias as $c )
        {
            if ( !$c instanceof Criteria )
            {
                throw new \InvalidArgumentException( "At least one of provided criterias is not a valid Criteria object" );
            }

            $this->criterias[] = $c;
        }
    }
}
?>
