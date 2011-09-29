<?php
/**
 * File containing DomainObject interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;

/**
 * Interface for domain objects
 *
 */
interface ModelInterface
{
    /**
     * Sets internal variables on object from array
     *
     * Key is property name and value is property value.
     *
     * @internal
     * @param array $state
     * @return \ezp\Base\ModelInterface
     * @throws \ezp\Base\Exception\PropertyNotFound If one of the properties in $state is not found
     */
    public function setState( array $state );

    /**
     * Gets internal variables on object as array
     *
     * Key is property name and value is property value.
     *
     * @internal
     * @param string|null $property Optional, lets you specify to only return one property by name
     * @return array|mixed Array if $property is null, else value of property
     * @throws \ezp\Base\Exception\PropertyNotFound If $property is not found (when not null)
     */
    public function getState( $property = null );
}
?>
