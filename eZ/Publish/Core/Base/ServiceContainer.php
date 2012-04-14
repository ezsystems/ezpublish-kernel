<?php
/**
 * Service Container class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base;
use eZ\Publish\Core\Base\Exceptions\BadConfiguration,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    eZ\Publish\Core\Base\Exceptions\MissingClass,
    eZ\Publish\API\Container,
    ReflectionClass;

/**
 * Service container class
 *
 * A dependency injection container that uses configuration for defining dependencies.
 *
 * Usage:
 *
 *     $sc = new eZ\Publish\Core\Base\ServiceContainer( $configManager->getConfiguration('service')->getAll() );
 *     $sc->getRepository->getContentService()...;
 *
 * Or overriding $dependencies (in unit tests):
 * ( $dependencies keys should have same value as service.ini "arguments" values explained bellow )
 *
 *     $sc = new eZ\Publish\Core\Base\ServiceContainer(
 *         $configManager->getConfiguration('service')->getAll(),
 *         array(
 *             '@persistence_handler' => new \eZ\Publish\Core\Persistence\InMemory\Handler()
 *         )
 *     );
 *     $sc->getRepository->getContentService()...;
 *
 * Settings are defined in service.ini like the following example:
 *
 *     [repository]
 *     public=true
 *     class=eZ\Publish\Core\Base\Repository
 *     arguments[persistence_handler]=@inmemory_persistence_handler
 *
 *     [inmemory_persistence_handler]
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
        $this->settings = $settings;
        $this->dependencies = $dependencies + array(
            '$_SERVER' => $_SERVER,
            '$_REQUEST' => $_REQUEST,
            '$_COOKIE' => $_COOKIE,
            '$_FILES' => $_FILES,
            '$_POST' => $_POST,
            '$_GET' => $_GET,
        );
    }

    /**
     * Service function to get Repository object
     *
     * Alias with type hints for $repo->get( 'repository' );
     *
     * @uses get()
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository()
    {
        if ( isset( $this->dependencies['@repository'] ) )
            return $this->dependencies['@repository'];
        return $this->internalGet( 'repository' );
    }

    /**
     * Get a variable dependency
     *
     * @param string $variable
     * @return mixed
     * @throws InvalidArgumentException
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
     * @uses internalGet()
     * @param string $serviceName
     * @return object
     */
    public function get( $serviceName )
    {
        return $this->internalGet( $serviceName );
    }

    /**
     * Get service by name (internal variant, exposes possibility to allow private services as used by dependencies)
     *
     * @uses lookupArguments()
     * @throws BadConfiguration
     * @throws MissingClass
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If dependency is private & !$allowPrivate
     * @param string $serviceName
     * @param bool $allowPrivate
     * @return object
     */
    protected function internalGet( $serviceName, $allowPrivate = false )
    {
        $serviceKey = "@{$serviceName}";

        // Return directly if it already exists
        if ( isset( $this->dependencies[$serviceKey] ) )
        {
            return $this->dependencies[$serviceKey];
        }

        if ( strpos( $serviceName, '-' ) )// If - is at a positive position, then service extends another one
        {
            $serviceParent = explode( '-', $serviceName );
            $serviceParent = '-' . $serviceParent[1];
            if ( empty( $this->settings[$serviceName] ) && empty( $this->settings[$serviceParent] ) )// Validate settings
            {
                throw new BadConfiguration( "service\\[{$serviceName}]", "no settings exist for '{$serviceName}'" );
            }
            else if ( empty( $this->settings[$serviceName] ) )
            {
                $settings = $this->settings[$serviceParent] + array( 'shared' => true, 'public' => false );
            }
            else
            {
                $settings = array_merge(
                    $this->settings[$serviceParent] + array( 'shared' => true, 'public' => false ),
                    $this->settings[$serviceName]
                );// uses array_merge on puposes to make sure arguments are reset
            }
        }
        else if ( empty( $this->settings[$serviceName] ) )// Validate settings
        {
            throw new BadConfiguration( "service\\[{$serviceName}]", "no settings exist for '{$serviceName}'" );
        }
        else
        {
            $settings = $this->settings[$serviceName] + array( 'shared' => true, 'public' => false );
        }

        if ( empty( $settings['class'] ) )
        {
            throw new BadConfiguration( "service\\[{$serviceName}]\\class", 'class setting is not defined' );
        }

        if ( $allowPrivate === false && $settings['public'] === false )
        {
            throw new InvalidArgumentException(
                "service\\[{$serviceName}]",
                'It is marked as private & can only be used as a dependency'
            );
        }

        if ( !class_exists( $settings['class'] ) )
        {
            throw new MissingClass( $settings['class'], 'service' );
        }

        // Create service directly if it does not have any arguments
        if ( empty( $settings['arguments'] ) )
        {
            if ( $settings['shared'] )
                return $this->dependencies[$serviceKey] = new $settings['class']();

            return new $settings['class']();
        }

        // Expand arguments with other service objects on arguments that start with @ and predefined variables that start with $
        $argumentKeys = array();
        $arguments = $this->lookupArguments( $settings['arguments'], $argumentKeys );

        // Use "new" if just 1 or 2 arguments (premature optimization to avoid use of reflection in most cases)
        if ( isset( $argumentKeys[0] ) && !isset( $argumentKeys[2] ) )
        {
            if ( !isset( $argumentKeys[1] ) )
                $serviceObject = new $settings['class']( $arguments[ $argumentKeys[0] ] );
            else
                $serviceObject = new $settings['class']( $arguments[ $argumentKeys[0] ], $arguments[ $argumentKeys[1] ] );
        }
        else // use Reflection to create a new instance, using the $args
        {
            $reflectionObj = new ReflectionClass( $settings['class'] );
            $serviceObject =  $reflectionObj->newInstanceArgs( $arguments );
        }

        if ( $settings['shared'] )
            $this->dependencies[$serviceKey] = $serviceObject;

        return $serviceObject;
    }

    /**
     * Lookup arguments for variable, service or arrays for recursive lookup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If undefined variable is used.
     * @param array $arguments
     * @param array &$keys Optional, keys in array will be appended in the order they are found (but not recursively)
     * @return array
     */
    protected function lookupArguments( array $arguments, array &$keys = array() )
    {
        $serviceContainer = $this;
        $builtArguments = array();
        foreach ( $arguments as $key => $argument )
        {
            $keys[] = $key;
            if ( isset( $argument[0] ) && ( $argument[0] === '$' || $argument[0] === '@'  || $argument[0] === '%' ) )
            {
                $function = '';
                if ( stripos( $argument, '::' ) !== false )
                {
                    // Check if argument is a callback
                    list( $argument, $function  ) = explode( '::', $argument );
                }

                if ( ( $argument[0] === '%' || $argument[0] === '@' ) && $argument[1] === '-' )// expand extended services
                {
                    $builtArguments[$key] = $this->lookupArguments( $this->expandExtendedServices( $argument, $function ) );
                    continue;
                }
                elseif ( $argument[0] === '%' )// lazy loaded services
                {
                    if ( $function !== '' )
                        $builtArguments[$key] = function() use ( $serviceContainer, $argument, $function ){
                            $service = $serviceContainer->get( ltrim( $argument, '%' ) );
                            return call_user_func_array( array( $service, $function ), func_get_args() );
                        };
                    else
                        $builtArguments[$key] = function() use ( $serviceContainer, $argument ){
                            return $serviceContainer->get( ltrim( $argument, '%' ) );
                        };
                }
                else if ( isset( $this->dependencies[ $argument ] ) )// Existing dependencies (@Service / $Variable)
                {
                    $builtArguments[$key] = $this->dependencies[ $argument ];
                }
                else if ( $argument[0] === '$' )// Undefined variables will trow an exception
                {
                    throw new InvalidArgumentValue( "arguments[{$key}]", $argument );
                }
                else// Try to load a @service dependency
                {
                    $builtArguments[$key] = $this->internalGet( ltrim( $argument, '@' ), true );
                }

                if ( $function !== '' && $argument[0] !== '%' )
                {
                    $builtArguments[$key] = array( $builtArguments[$key], $function );
                }
            }
            else if ( is_array( $argument ) )
            {
                $builtArguments[$key] = $this->lookupArguments( $argument );
            }
            else // Scalar values
            {
                $builtArguments[$key] = $argument;
            }
        }
        return $builtArguments;
    }

    /**
     * @param string $argument Eg: %-controller
     * @param string $function Optional function string
     * @return array|int|string
     */
    protected function expandExtendedServices( $argument, $function = '' )
    {
        $prefix = $argument[0];
        $argument = ltrim( $argument, '@%' );
        $services = array();
        if ( $function !== '' )
            $function = '::' . $function;

        foreach ( $this->settings as $service => $settings )
        {
            if ( preg_match( "/^(?P<name>\w+){$argument}$/", $service, $match ) )
            {
                $services[$match['name']] = $prefix . $match['name'] . $argument . $function;
            }
        }
        return $services;
    }
}
