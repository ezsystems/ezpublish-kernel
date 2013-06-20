<?php
/**
 * File containing the ParameterWrapper class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Templating;

use eZ\Publish\Core\MVC\Legacy\Templating\LegacyCompatible;
use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException;

/**
 * Wrapper for parameters injected as variables into view templates.
 */
class ParameterWrapper implements LegacyCompatible
{
    /**
     * @var array
     */
    private $params;

    public function __construct( array $params = array() )
    {
        $this->params = $params;
    }

    /**
     * Magic get function handling read to wrapped parameters.
     *
     * Returns value for all wrapped parameters.
     *
     * @ignore This method is for internal use
     * @access private
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException exception on all reads to undefined parameters so typos are not silently accepted.
     *
     * @param string $name Name of the parameter.
     *
     * @return mixed
     */
    public function __get( $name )
    {
        if ( !isset( $this->params[$name] ) )
        {
            throw new PropertyNotFoundException( $name, get_class( $this ) );
        }

        return $this->params[$name];
    }

    public function __isset( $name )
    {
        return isset( $this->params[$name] );
    }

    /**
     * Returns true if object supports attribute $name
     *
     * @param string $name
     *
     * @return boolean
     */
    public function hasAttribute( $name )
    {
        return $this->__isset( $name );
    }

    /**
     * Returns the value of attribute $name.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException If $name is not supported as a valid attribute
     *
     * @return mixed
     */
    public function attribute( $name )
    {
        return $this->__get( $name );
    }

    /**
     * Returns an array of supported attributes (only their names).
     *
     * @return array
     */
    public function attributes()
    {
        return array_keys( $this->params );
    }
}
