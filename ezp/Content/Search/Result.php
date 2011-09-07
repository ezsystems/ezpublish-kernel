<?php
/**
 * File containing Result collection class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace ezp\Content\Search;
use ezp\Base\Collection\Type as TypeCollection;

/**
 * Result collection class
 * Holds results returned by a search
 */
class Result extends TypeCollection
{
    /**
     * Constructor
     * @param \ezp\Content[] $elements
     */
    public function __construct( array $elements )
    {
        parent::__construct( 'ezp\\Content', $elements );
    }
}
