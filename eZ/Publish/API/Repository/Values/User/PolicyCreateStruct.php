<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\PolicyCreateStruct class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to create a policy
 *
 * @property string $module @deprecated alias for $controller
 * @property string $function @deprecated alias for $action
 */
abstract class PolicyCreateStruct extends ValueObject
{
    /**
     * The controller (legacy:module) identifier
     *
     * Eg: 'Bundle:controller' or 'content'
     *
     * @var string
     */
    public $controller;

    /**
     * Controller action (legacy:function), unique within controller
     *
     * Eg: 'read' or '*'
     *
     * @var string
     */
    public $action;

    /**
     * Returns list of limitations added to policy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    abstract public function getLimitations();

    /**
     * Adds a limitation with the given identifier and list of values
     * @param Limitation $limitation
     */
    abstract public function addLimitation( Limitation $limitation );

    /**
     * Magic getter for supporting $module and $function
     *
     * @deprecated Since 5.1
     * @param string $property The name of the property to retrieve
     *
     * @return mixed
     */
    public function __get( $property )
    {
        if ( $property === 'module' )
            return $this->controller;

        if ( $property === 'function' )
            return $this->action;

        return parent::__get( $property );
    }

    /**
     * Magic set for supporting $module and $function
     *
     * @deprecated Since 5.1
     * @param string $property
     * @param mixed $propertyValue
     */
    public function __set( $property, $propertyValue )
    {
        if ( $property === 'module' )
            $this->controller = $propertyValue;
        else if ( $property === 'function' )
            $this->action = $propertyValue;
        else
            parent::__set( $property, $propertyValue );
    }

    /**
     * Magic isset for supporting $module and $function
     *
     * @deprecated Since 5.1
     * @param string $property
     *
     * @return boolean
     */
    public function __isset( $property )
    {
        if ( $property === 'module' )
            return true;

        if ( $property === 'function' )
            return true;

        return parent::__isset( $property );
    }
}
