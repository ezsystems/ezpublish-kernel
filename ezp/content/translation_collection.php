<?php
/**
 * File containing the ezp\Content\TranslationCollection class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage Content
 */

/**
 * This class represents a Content translations collection
 *
 * @package API
 * @subpackage Content
 */
namespace ezp\Content;

class TranslationCollection extends BaseCollection
{

    /**
     * Will throw an exception as fieldsets are not directly writeable
     * @param mixed $offset
     * @param mixed $value
     * @throws ezcBasePropertyPermissionException
     */
    public function offsetSet( $offset, $value )
    {
        throw new \ezcBasePropertyPermissionException( "translations", \ezcBasePropertyPermissionException::READ );
    }

    /**
     * Will throw an exception as fieldsets are not directly writeable
     * @param mixed $offset
     * @param mixed $value
     * @throws ezcBasePropertyPermissionException
     */
    public function offsetUnset( $offset )
    {
        throw new \ezcBasePropertyPermissionException( "translations", \ezcBasePropertyPermissionException::READ );
    }
}
?>
