<?php
/**
 * File containing the ezp\Content\FieldCollection class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage Content
 */

/**
 * This class represents a Content fieldset collection
 * Fieldsets held in a FieldsetCollection object are accessible via array access,
 * indexed by locale
 *
 * @package API
 * @subpackage Content
 */
namespace ezp\Content;

class FieldCollection extends BaseCollection
{

    /**
     * Will throw an exception as fieldsets are not directly writeable
     * @param mixed $offset
     * @param mixed $value
     * @throws ezcBasePropertyPermissionException
     */
    public function offsetSet( $offset, $value )
    {
        throw new \ezcBasePropertyPermissionException( "fieldsets", \ezcBasePropertyPermissionException::READ );
    }

    /**
     * Will throw an exception as fieldsets are not directly writeable
     * @param mixed $offset
     * @param mixed $value
     * @throws ezcBasePropertyPermissionException
     */
    public function offsetUnset( $offset )
    {
        throw new \ezcBasePropertyPermissionException( "fieldsets", \ezcBasePropertyPermissionException::READ );
    }
}
?>
