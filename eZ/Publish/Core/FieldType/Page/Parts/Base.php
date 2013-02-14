<?php
/**
 * File containing the Base class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Page\Parts;

use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException;
use eZ\Publish\Core\FieldType\Page\Service as PageService;

class Base
{
    /**
     * Object properties container
     *
     * @var array
     */
    protected $properties = array();

    /**
     * Service container
     *
     * @var \eZ\Publish\Core\FieldType\Page\Service
     */
    protected $pageService;

    /**
     * Constructor
     *
     * @param \eZ\Publish\Core\FieldType\Page\Service $pageService
     */
    public function __construct( PageService $pageService )
    {
        $this->pageService = $pageService;
    }

    /**
     * Sets property $value for a given $name
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set( $name, $value )
    {
        $this->properties[$name] = $value;
    }

    /**
     * Returns property value for a given $name
     *
     * @param string $name
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException
     *
     * @return null
     */
    public function __get( $name )
    {
        if ( isset( $this->properties[$name] ) )
            return $this->properties[$name];

        throw new PropertyNotFoundException( $name, get_class( $this ) );
    }

    /**
     * Checks whether property for given $name exists
     *
     * @param string $name
     *
     * @return boolean
     */
    public function __isset( $name )
    {
        return isset( $this->properties[$name] );
    }

    /**
     * Getter for property list
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }
}
