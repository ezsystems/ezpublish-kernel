<?php
/**
 * Service Container class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base;

use eZ\Publish\Core\Base\Exceptions\BadConfiguration;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\MissingClass;
use eZ\Publish\API\Container;
use ReflectionClass;

/**
 * Service container class
 *
 * A dependency injection container that uses configuration for defining dependencies.
 *
 * Usage:
 *
 *     $sc = new eZ\Publish\Core\Base\ServiceContainer( $configManager->getConfiguration( 'service' )->getAll() );
 *     $sc->getRepository->getContentService()...;
 *
 * Or overriding $dependencies (in unit tests):
 * ( $dependencies keys should have same value as service.ini "arguments" values explained bellow )
 *
 *     $sc = new eZ\Publish\Core\Base\ServiceContainer(
 *         $configManager->getConfiguration( 'service' )->getAll(),
 *         array(
 *             '@persistence_handler' => new \eZ\Publish\Core\Persistence\InMemory\Handler()
 *         )
 *     );
 *     $sc->getRepository->getContentService()...;
 *
 * Settings are defined in service.ini like the following example:
 *
 *     [repository]
 *     class=eZ\Publish\Core\Base\Repository
 *     arguments[persistence_handler]=@persistence_handler_inmemory
 *
 *     [persistence_handler_inmemory]
 *     class=eZ\Publish\Core\Persistence\InMemory\Handler
 *
 *     # @see \eZ\Publish\Core\settings\service.ini For more options and examples.
 *
 * "arguments" values in service.ini can start with either @ in case of other services being dependency, $ if a
 * predefined global variable is to be used ( currently: $_SERVER, $_REQUEST, $_COOKIE, $_FILES )
 * or plain scalar if that is to be given directly as argument value.
 * If the argument value starts with %, then it is a lazy loaded service provided as a callback (closure).
 */
class ServiceContainer implements Container
{
    /**
     * Holds service objects and variables
     *
     * @var object[]
     */
    private $dependencies;

    /**
     * Array of optional settings overrides
     *
     * @var array[]
     */
    private $settings;

    /**
     * Construct object with optional configuration overrides
     *
     * @param array $settings Services settings
     * @param mixed[]|object[] $dependencies Optional initial dependencies
     */
    public function __construct( array $settings, array $dependencies = array() )
    {
        // Set parameters as $dependencies, globals and settings parameters
        $parameters = array(
            '$_SERVER' => $_SERVER,
            '$_REQUEST' => $_REQUEST,
            '$_COOKIE' => $_COOKIE,
            '$_FILES' => $_FILES,
            '$_POST' => $_POST,
            '$_GET' => $_GET,
        );

        if ( !empty( $settings['parameters'] ) )
        {
            foreach ( $settings['parameters'] as $parameterKey => $parameter )
            {
                $parameters['$' . $parameterKey ] = $parameter;
            }
            unset( $settings['parameters'] );
        }

        // Set properties
        $this->settings = $settings;
        $this->dependencies = $dependencies + $parameters;
    }

    /**
     * Service function to get Repository object
     *
     * Alias with type hints for $repo->get( 'repository' );
     *
     * @uses get()
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository()
    {
        if ( isset( $this->dependencies['@repository'] ) )
            return $this->dependencies['@repository'];
        return $this->get( 'repository' );
    }

    /**
     * Get a variable dependency
     *
     * @param string $variable
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     *
     * @return mixed
     */
    public function getVariable( $variable )
    {
        $variableKey = "\${$variable}";
        if ( isset( $this->dependencies[$variableKey] ) )
        {
            return $this->dependencies[$variableKey];
        }

        throw new InvalidArgumentException(
            "{$variableKey}",
            'Could not find this variable among existing dependencies'
        );
    }

    /**
     * Get service by name
     *
     * @uses lookupArguments()
     * @throws \eZ\Publish\Core\Base\Exceptions\BadConfiguration
     * @throws \eZ\Publish\Core\Base\Exceptions\MissingClass
     * @param string $serviceName
     *
     * @return object
     */
    public function get( $serviceName )
    {
        // If you have endless loop here; Congrats, you have recursive aliases!
        do
        {
            // Return directly if it already exists
            if ( isset( $this->dependencies["@{$serviceName}"] ) )
                return $this->dependencies["@{$serviceName}"];

            // Validate settings
            if ( empty( $this->settings[$serviceName] ) )
                throw new BadConfiguration( "service\\[{$serviceName}]", "no settings exist for '{$serviceName}'" );

            $origServiceName = $serviceName;
            if ( isset( $this->settings[$serviceName]['alias'] ) )
                $serviceName = $this->settings[$serviceName]['alias'];
        }
        while ( $origServiceName !== $serviceName );

        $settings = $this->settings[$serviceName] + array( 'shared' => true );
        if ( empty( $settings['class'] ) )
        {
            throw new BadConfiguration( "service\\[{$serviceName}]\\class", 'class setting is not defined' );
        }
        else if ( !class_exists( $settings['class'] ) )
        {
            throw new MissingClass( $settings['class'], 'service' );
        }

        // Expand arguments with other service objects on arguments that start with @ and predefined variables that start with $
        if ( !empty( $settings['arguments'] ) )
        {
            $arguments = $this->lookupArguments( $settings['arguments'], true );
        }
        else
        {
            $arguments = array();
        }

        // Create new object
        if ( !empty( $settings['factory'] ) )
        {
            $serviceObject = call_user_func_array( "{$settings['class']}::{$settings['factory']}", $arguments );
        }
        else if ( empty( $arguments ) )
        {
            $serviceObject = new $settings['class']();
        }
        else if ( isset( $arguments[0] ) && !isset( $arguments[2] ) )
        {
            if ( !isset( $arguments[1] ) )
                $serviceObject = new $settings['class']( $arguments[0] );
            else
                $serviceObject = new $settings['class']( $arguments[0], $arguments[1] );
        }
        else
        {
            $reflectionObj = new ReflectionClass( $settings['class'] );
            $serviceObject = $reflectionObj->newInstanceArgs( $arguments );
        }

        if ( $settings['shared'] )
            $this->dependencies["@{$serviceName}"] = $serviceObject;

        if ( !empty( $settings['method'] ) )
        {
            $list = $this->recursivelyLookupArguments( $settings['method'] );
            foreach ( $list as $methodName => $arguments )
            {
                foreach ( $arguments as $argumentKey => $argumentValue )
                    $serviceObject->$methodName( $argumentValue, $argumentKey );
            }
        }

        return $serviceObject;
    }

    /**
     * Lookup arguments for variable, service or arrays for recursive lookup
     *
     * 1. Does not keep keys of first level arguments
     * 2. Exists loop when it encounters optional non existing service dependencies
     *
     * @uses getServiceArgument()
     * @uses recursivelyLookupArguments()
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If undefined variable is used.
     * @param array $arguments
     * @param boolean $recursively
     *
     * @return array
     */
    protected function lookupArguments( array $arguments, $recursively = false )
    {
        $builtArguments = array();
        foreach ( $arguments as $argument )
        {
            if ( isset( $argument[0] ) && ( $argument[0] === '$' || $argument[0] === '@' || $argument[0] === '%' ) )
            {
                $serviceObject = $this->getServiceArgument( $argument );
                if ( $argument[1] === '?' && $serviceObject === null )
                    break;

                $builtArguments[] = $serviceObject;
            }
            else if ( $recursively && is_array( $argument ) )
            {
                $builtArguments[] = $this->recursivelyLookupArguments( $argument );
            }
            // Scalar values
            else
            {
                $builtArguments[] = $argument;
            }
        }
        return $builtArguments;
    }

    /**
     * Lookup arguments for variable, service or arrays for recursive lookup
     *
     * 1. Keep keys of arguments
     * 2. Does not exit loop on optional non existing service dependencies
     *
     * @uses getServiceArgument()
     * @uses recursivelyLookupArguments()
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If undefined variable is used.
     * @param array $arguments
     *
     * @return array
     */
    protected function recursivelyLookupArguments( array $arguments )
    {
        $builtArguments = array();
        foreach ( $arguments as $key => $argument )
        {
            if ( isset( $argument[0] ) && ( $argument[0] === '$' || $argument[0] === '@' || $argument[0] === '%' ) )
            {
                $serviceObject = $this->getServiceArgument( $argument );
                if ( $argument[1] !== '?' || $serviceObject !== null )
                    $builtArguments[$key] = $serviceObject;
            }
            else if ( is_array( $argument ) )
            {
                $builtArguments[$key] = $this->recursivelyLookupArguments( $argument );
            }
            // Scalar values
            else
            {
                $builtArguments[$key] = $argument;
            }
        }
        return $builtArguments;
    }

    /**
     * @uses getListOfExtendedServices()
     * @uses recursivelyLookupArguments()
     * @param string $argument
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     *
     * @return array|closure|mixed|object|null Null on non existing optional dependencies
     */
    protected function getServiceArgument( $argument )
    {
        $function = '';
        $serviceContainer = $this;
        // callback
        if ( stripos( $argument, '::' ) !== false )
            list( $argument, $function  ) = explode( '::', $argument );

        // expand extended services
        if ( ( $argument[0] === '%' || $argument[0] === '@' ) && $argument[1] === ':' )
        {
            return $this->recursivelyLookupArguments( $this->getListOfExtendedServices( $argument, $function ) );
        }
        // lazy loaded services
        else if ( $argument[0] === '%' )
        {
            // Optional dependency handling
            if ( $argument[1] === '?' && !isset( $this->settings[substr( $argument, 2 )] ) )
                return null;

            if ( $function !== '' )
                return function () use ( $serviceContainer, $argument, $function )
                {
                    $serviceObject = $serviceContainer->get( ltrim( $argument, '%' ) );
                    return call_user_func_array( array( $serviceObject, $function ), func_get_args() );
                };
            else
                return function () use ( $serviceContainer, $argument )
                {
                    return $serviceContainer->get( ltrim( $argument, '%' ) );
                };
        }
        // Existing dependencies (@Service / $Variable)
        else if ( isset( $this->dependencies[ $argument ] ) )
        {
            $serviceObject = $this->dependencies[ $argument ];
        }
        // Undefined variables will trow an exception
        else if ( $argument[0] === '$' )
        {
            // Optional dependency handling
            if ( $argument[1] === '?' )
                return null;

            throw new InvalidArgumentValue( "\$arguments", $argument );
        }
        // Try to load a @service dependency
        else
        {
            // Optional dependency handling
            if ( $argument[1] === '?' && !isset( $this->settings[substr( $argument, 2 )] ) )
                return null;

            $serviceObject = $this->get( ltrim( $argument, '@' ) );
        }

        if ( $function !== '' )
            return array( $serviceObject, $function );

        return $serviceObject;
    }

    /**
     * @param string $parent Eg: %:controller
     * @param string $function Optional function string
     *
     * @return array
     */
    protected function getListOfExtendedServices( $parent, $function = '' )
    {
        $prefix = $parent[0];
        $parent = ltrim( $parent, '@%' );// Keep starting ':' on parent for easier matching bellow
        $services = array();
        if ( $function !== '' )
            $function = '::' . $function;

        foreach ( $this->settings as $service => $settings )
        {
            if ( stripos( $service, $parent ) !== false &&
                 !empty( $settings['class'] ) &&
                 preg_match( "/^(?P<prefix>[\w:]+){$parent}$/", $service, $match ) )
            {
                $services[$match['prefix']] = $prefix . $match['prefix'] . $parent . $function;
            }
        }
        return $services;
    }
}
