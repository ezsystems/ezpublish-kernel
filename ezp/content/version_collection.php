<?php
/**
 * File containing the ezp\Content\VersionCollection class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage Content
 */

/**
 * This class represents a collection of Content Versions
 *
 * @package API
 * @subpackage Content
 */
namespace ezp\Content;

class VersionCollection extends Base implements ContentDomainInterface
{
    /**
     * Restores the state of a content object
     * @param array $objectValue
     */
    public static function __set_state( array $state )
    {
        $obj = new self;
        foreach ( $state as $property => $value )
        {
            if ( isset( $obj->properties[$property] ) )
            {
                $obj->properties[$property] = $value;
            }
        }

        return $obj;
    }
}
?>