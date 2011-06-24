<?php
/**
 * File containing FieldForwarder class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage content/criteria
 */
namespace ezp\content\Criteria;

/**
 * Forwards access to a new FieldCriteria
 * Only read access is allowed
 */
class FieldForwarder implements \ArrayAccess
{
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists( $offset )
    {
        return false;
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet( $offset )
    {
        return new FieldCriteria( $offset );
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet( $offset, $value )
    {
        throw new InvalidArgumentException( "Write access is not allowed" );
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset( $offset )
    {
        return;
    }
}
?>
