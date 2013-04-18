<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\Policy class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a policy value
 *
 * @property-read mixed $id internal id of the policy
 * @property-read mixed $roleId the role id this policy belongs to
 * @property-read string $controller The controller (legacy:module) identifier, example: 'Bundle:controller' or 'content'
 * @property-read string $action Controller action (legacy:function), unique within controller: 'read', or all: '*'
 * @property-read array $limitations an array of \eZ\Publish\API\Repository\Values\User\Limitation
 *
 * @property-read string $module @deprecated alias for $controller
 * @property-read string $function @deprecated alias for $action
 */
abstract class Policy extends ValueObject
{
    /**
     * Re route deprecated module & function properties to controller & action
     *
     * @deprecated Since 5.1
     * @param array $properties
     */
    public function __construct( array $properties = array() )
    {
        if ( isset( $properties['module'] ) )
        {
            $properties['controller'] = $properties['module'];
            unset( $properties['module'] );
        }
        if ( isset( $properties['function'] ) )
        {
            $properties['action'] = $properties['function'];
            unset( $properties['function'] );
        }

        return parent::__construct( $properties );
    }

    /**
     * ID of the policy
     *
     * @var mixed
     */
    protected $id;

    /**
     * the ID of the role this policy belongs to
     *
     * @var mixed
     */
    protected $roleId;

    /**
     * The controller (legacy:module) identifier
     *
     * Eg: 'Bundle:controller' or 'content'
     *
     * @var string
     */
    protected $controller;

    /**
     * Name of the module function Or all functions with '*'
     *
     * Eg: read
     *
     * @var string
     */
    protected $action;

    /**
     * @return \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    abstract public function getLimitations();

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
            return parent::__get( 'controller' );

        if ( $property === 'function' )
            return parent::__get( 'action' );

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
            parent::__set( 'controller', $propertyValue );
        else if ( $property === 'function' )
            parent::__set( 'action', $propertyValue );
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
