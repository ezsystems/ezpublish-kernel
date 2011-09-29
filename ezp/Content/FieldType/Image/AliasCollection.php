<?php
/**
 * File containing the AliasCollection class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Image;
use ezp\Base\Collection\Type as TypeCollection;

/**
 * Image alias collection.
 * This collection can only hold image Alias objects
 */
class AliasCollection extends TypeCollection
{
    /**
     * Image alias handler
     *
     * @var \ezp\Content\FieldType\Image\Handler
     */
    protected $aliasHandler;

    public function __construct( Handler $aliasHandler, array $elements = array() )
    {
        $this->aliasHandler = $aliasHandler;
        parent::__construct( 'ezp\\Content\\FieldType\\Image\\Alias', $elements );
    }

    /**
     * @todo Here gets an alias with its alias name, and create it if needed
     * @param type $index
     * @return type
     */
    public function offsetGet( $index )
    {
        return parent::offsetGet( $index );
    }
}
