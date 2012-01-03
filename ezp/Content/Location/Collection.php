<?php
/**
 * File containing Location collection class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace ezp\Content\Location;
use ezp\Base\Collection\Type as TypeCollection;

/**
 * Location collection class
 */
class Collection extends TypeCollection
{
    /**
     * Constructor
     * @param \ezp\Content\Location[] $elements
     */
    public function __construct( array $elements )
    {
        parent::__construct( 'ezp\\Content\\Location', $elements );
    }
}
