<?php
/**
 * Service Container class
 *
 * @copyright Copyright (C) 2012 andrerom & eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;
use ezp\Base\ConfigurationManager,
    ezp\Base\Exception\BadConfiguration,
    ezp\Base\Exception\InvalidArgumentValue,
    ezp\Base\Exception\MissingClass,
    ReflectionClass;

/**
 * Service container class
 *
 * A dependency injection container that uses configuration for defining dependencies.
 *
 * Usage:
 *
 *     $sc = new ezp\Base\ServiceContainer( Configuration::getInstance('service')->getAll() );
 *     $sc->getRepository->getContentService()...;
 *
 * Or overriding $dependencies (in unit tests):
 * ( $dependencies keys should have same value as service.ini "arguments" values explained bellow )
 *
 *     $sc = new ezp\Base\ServiceContainer(
 *         Configuration::getInstance('service')->getAll(),
 *         array(
 *             '@persistence_handler' => new \ezp\Persistence\Storage\InMemory\Handler()
 *         )
 *     );
 *     $sc->getRepository->getContentService()...;
 *
 * Settings are defined in service.ini like the following example:
 *
 *     [repository]
 *     shared=true
 *     class=ezp\Base\Repository
 *     arguments[persistence_handler]=@inmemory_persistence_handler
 *
 *     [inmemory_persistence_handler]
 *     shared=true
 *     class=ezp\Persistence\Storage\InMemory\Handler
 *
 *     @todo Update service.ini reference bellow
 *     # @see \ezp\Base\settings\service.ini For more options and examples.
 *
 * "arguments" values in service.ini can start with either @ in case of other services being dependency, $ if a
 * predefined global variable is to be used ( currently: $_SERVER, $_REQUEST, $_COOKIE, $_FILES and $serviceContainer )
 * or plain scalar if that is to be given directly as argument value.
 */
class ServiceContainer
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
        );
    }

    /**
     * Service function to get ConfigurationManager object
     *
     * Alias with type hints for $repo->get( 'configuration' );
     *
     * @uses get()
     * @return \ezp\Base\ConfigurationManager
     */
    public function getConfigurationManager()
    {
        if ( isset( $this->dependencies['@configuration'] ) )
            return $this->dependencies['@configuration'];
        // will not work as ConfigurationManager has dependencies on config.php settings at least
        return $this->get( 'configuration' );
    }

    /**
     * Service function to get Repository object
     *
     * Alias with type hints for $repo->get( 'repository' );
     *
     * @uses get()
     * @return \ezp\PublicAPI\Interfaces\Repository
     */
    public function getRepository()
    {
        if ( isset( $this->dependencies['@repository'] ) )
            return $this->dependencies['@repository'];
        return $this->get( 'repository' );
    }

    /**
     * Get service by name
     *
     * @uses lookupArguments()
     * @throws BadConfiguration
     * @throws MissingClass
     * @param string $serviceName
     * @return object
     */
    public function get( $serviceName )
    {
        $serviceKey = "@{$serviceName}";

        // Return directly if it already exists
        if ( isset( $this->dependencies[$serviceKey] ) )
        {
            return $this->dependencies[$serviceKey];
        }

        // Validate settings
        if ( empty( $this->settings[$serviceName] ) )
        {
            throw new BadConfiguration( "service\\[{$serviceName}]", "no settings exist for '{$serviceName}'" );
        }

        $settings = $this->settings[$serviceName] + array( 'shared' => false );
        if ( empty( $settings['class'] ) )
        {
            throw new BadConfiguration( "service\\[{$serviceName}]\\class", 'class setting is not defined' );
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
     * @throws InvalidArgumentValue
     * @param array $arguments
     * @param array &$keys Optional, keys in array will be appended in the order they are found (but not recursively)
     * @return array
     */
    protected function lookupArguments( array $arguments, array &$keys = array() )
    {
        $builtArguments = array();
        foreach ( $arguments as $key => $argument )
        {
            if ( isset( $argument[0] ) && ( $argument[0] === '$' || $argument[0] === '@' ) )
            {
                if ( $argument === '$serviceContainer' )
                {
                    // Self
                    $builtArguments[$key] = $this;
                }
                else if ( isset( $this->dependencies[ $argument ] ) )
                {
                    // Existing dependencies (@Service / $Variable)
                    $builtArguments[$key] = $this->dependencies[ $argument ];
                }
                else if ( $argument[0] === '$' )
                {
                    // Undefined variables will trow an exception
                    throw new InvalidArgumentValue( "arguments[{$key}]", $argument );
                }
                else
                {
                    // Try to load a @service dependency
                    $builtArguments[$key] = $this->get( ltrim( $argument, '@' ) );
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
            $keys[] = $key;
        }
        return $builtArguments;
    }
}
