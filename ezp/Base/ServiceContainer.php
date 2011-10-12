<?php
/**
 * Service Container class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;
use ezp\Base\Exception\BadConfiguration,
    ezp\Base\Exception\InvalidArgumentValue,
    ezp\Base\Exception\MissingClass,
    ReflectionClass;

/**
 * Service container class
 *
 * A dependency injection container (DIC) that uses configuration for defining dependencies.
 *
 * Usage:
 *
 *     $sc = new ezp\Base\ServiceContainer();
 *     $sc->getRepository->getContentService()->load( 42 );
 *
 * Or overriding dependencies (in unit tests):
 *
 *     $sc = new ezp\Base\ServiceContainer( array( '@persistence_handler' => new \ezp\Persistence\Storage\InMemory\Handler() ) );
 *     $sc->getRepository->getContentService()->load( 42 );
 *
 * Settings are defined in service.ini like the following example:
 *
 *     [repository]
 *     class=ezp\Base\Repository
 *     arguments[persistence_handler]=@inmemory_persistence_handler
 *
 *     [inmemory_persistence_handler]
 *     class=ezp\Persistence\Storage\InMemory\Handler
 *
 * Arguments can start with either @ in case of other services being dependency, $ if a predefined global variable
 * is to be used ( currently: $_SERVER, $_REQUEST, $_COOKIE, $_FILES and $serviceContainer ) or plain string if
 * that is to be given directly as argument value.
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
     * @param array[] $settings
     * @param mixed[]|object[] $dependencies Optional initial dependencies
     */
    public function __construct( array $settings, array $dependencies = array() )
    {
        $this->settings = $settings;
        $this->dependencies = $dependencies +
            array(
                '$_SERVER' => $_SERVER,
                '$_REQUEST' => $_REQUEST,
                '$_COOKIE' => $_COOKIE,
                '$_FILES' => $_FILES
            );
    }

    /**
     * Service function to get Repository object
     *
     * @return Repository
     */
    public function getRepository()
    {
        if ( isset( $this->dependencies['@repository'] ) )
            return $this->dependencies['@repository'];
        return $this->get( 'repository' );
    }

    /**
     * @return BinaryRepository
     */
    public function getBinaryRepository()
    {
        if ( isset( $this->dependencies['@binary_repository'] ) )
            return $this->dependencies['@binary_repository'];
        return $this->get( 'binary_repository' );
    }

    /**
     * Get service by name
     *
     * @throws InvalidArgumentException
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
            throw new BadConfiguration( "base\\[{$serviceName}]", "no settings exist for '{$serviceName}'" );
        }

        $settings = $this->settings[$serviceName];
        if ( empty( $settings['class'] ) )
        {
            throw new BadConfiguration( "base\\[{$serviceName}]\\class", 'class setting is not defined' );
        }

        if ( !class_exists( $settings['class'] ) )
        {
            throw new MissingClass( $settings['class'], 'dependency' );
        }

        // Create service directly if it does not have any arguments
        if ( empty( $settings['arguments'] ) )
        {
            if ( !empty( $settings['factory'] ) )
                return $this->dependencies[$serviceKey] = $settings['class']::$settings['factory']();

            return $this->dependencies[$serviceKey] = new $settings['class']();
        }

        // Expand arguments with other service objects on arguments that start with @ and predefined variables that start with $
        $argumentKeys = array();
        $arguments = $this->lookupArguments( $settings['arguments'], $argumentKeys );


        // If factory use call_user_func_array
        if ( !empty( $settings['factory'] ) )
        {
            return $this->dependencies[$serviceKey] = call_user_func_array(
                array(
                     $settings['class'], $settings['factory']
                ),
                $arguments
            );
        }

        // Use "new" if just 1 or 2 arguments (premature optimization to avoid use of reflection in most cases)
        if ( isset( $argumentKeys[0] ) && !isset( $argumentKeys[2] ) )
        {
            if ( !isset( $argumentKeys[1] ) )
                return $this->dependencies[$serviceKey] = new $settings['class']( $arguments[ $argumentKeys[0] ] );

            return $this->dependencies[$serviceKey] = new $settings['class']( $arguments[ $argumentKeys[0] ], $arguments[ $argumentKeys[1] ] );
        }

        // use Reflection to create a new instance, using the $args
        $reflectionObj = new ReflectionClass( $settings['class'] );
        return $this->dependencies[$serviceKey] = $reflectionObj->newInstanceArgs( $arguments );
    }

    /**
     * Lookup arguments for variable, service or arrays for recursive lookup
     *
     * @param array $arguments
     * @param int $count Optional count of arguments in level provided
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
