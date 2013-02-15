<?php
/**
 * File containing the LegacyCompatible interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating;

/**
 * This interface must be implemented to make objects compatible with legacy eZ Template engine.
 */
interface LegacyCompatible
{
    /**
     * Returns true if object supports attribute $name
     *
     * @param string $name
     *
     * @return boolean
     */
    public function hasAttribute( $name );

    /**
     * Returns the value of attribute $name.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException If $name is not supported as a valid attribute
     *
     * @return mixed
     */
    public function attribute( $name );

    /**
     * Returns an array of supported attributes (only their names).
     *
     * @return array
     */
    public function attributes();
}
