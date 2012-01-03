<?php
/**
 * File contains Version Collection class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Version;
use ezp\Base\Collection\Type as TypeCollection;

/**
 * Version StaticCollection class.
 */
class StaticCollection extends TypeCollection
{
    /**
     * Constructor
     *
     * @param \ezp\Content\Version[] Versions to be added to the collection
     * @param int $contentId Id of content this version collection belongs to.
     */
    public function __construct( array $versions = array() )
    {
        parent::__construct( 'ezp\\Content\\Version', $versions );
    }
}
