<?php
/**
 * Service Container class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Service;
use ezp\Base\Configuration,
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
 *     $sc = new ezp\Base\Service\Container();
 *     $sc->GetRepository->GetContentService()->load( 42 );
 *
 * Or overriding dependencies (in unit tests):
 *
 *     $sc = new ezp\Base\Service\Container( array( '@repository_handler' => new \ezp\Persistence\Tests\InMemoryEngine\RepositoryHandler() ) );
 *     $sc->GetRepository->GetContentService()->load( 42 );
 *
 * Settings are defined in base.ini like the following example:
 *
 *     [service_repository]
 *     class=ezp\Base\Repository
 *     arguments[repository_handler]=@repository_handler
 *
 *     [service_repository_handler]
 *     class=ezp\Persistence\Tests\InMemoryEngine\RepositoryHandler
 *
 * Arguments can start with either @ in case of other services being dependency, $ if a predefined global variable
 * is to be used ( currently: $_SERVER, $_REQUEST, $_COOKIE, $_FILES and $serviceContainer ) or plain string if
 * that is to be given directly as argument value.
 *
 *
 * @todo Add support for factory functions, could simply check for existence of :: or -> for static / instance factories
 * @todo If needed add optional settings that define that service should be created on every call (not singleton)
 */
class Container
{
    /**
     * Holds service objects and variables
     *
     * @var object[]
     */
    private $dependencies;

    /**
     * Construct object with optional configuration overrides
     *
     * @param mixed[]|object[] $dependencies
     */
    public function __construct( array $dependencies = array() )
    {
        $this->dependencies = $dependencies +
            array(
                '$_SERVER' => $_SERVER,
                '$_REQUEST' => $_REQUEST,
                '$_COOKIE' => $_COOKIE,
                '$_FILES' => $_FILES
            );
    }

    /**
     * Service function to get Event instance.
     *
     * @return Interfaces\Event
     */
    public function getEvent( )
    {
        if ( isset( $this->dependencies['@event'] ) )
            return $this->dependencies['@event'];
        return $this->get( 'event' );
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
     * Get service by name
     *
     * @throws InvalidArgumentException
     * @param string $serviceName
     * @return object
     */
    public function get( $serviceName )
    {
        $serviceKey = "@{$serviceName}";

        // Return directly if already exists
        if ( isset( $this->dependencies[$serviceKey] ) )
        {
            return $this->dependencies[$serviceKey];
        }

        // Get settings
        $settings = Configuration::getInstance()->getSection( "service_{$serviceName}", false );

        // Validate settings
        if ( $settings === false )
        {
            throw new BadConfiguration( "base\\[service_{$serviceName}]", "no settings exist for '{$serviceName}'" );
        }
        else if ( empty( $settings['class'] ) )
        {
            throw new BadConfiguration( "base\\[service_{$serviceName}]\\class", 'class setting is not defined' );
        }
        else if ( !class_exists( $settings['class'] ) )
        {
            throw new MissingClass( $settings['class'], 'dependency' );
        }

        // Create service directly if it does not have any arguments
        if ( empty( $settings['arguments'] ) )
        {
            return $this->dependencies[$serviceKey] = new $settings['class']();
        }

        // Expand arguments with other service objects on arguments that start with @ and predefined variables that start with $
        $arguments = array();
        foreach ( $settings['arguments'] as $key => $argument )
        {
            // @Service / $Variable
            if ( isset( $argument[0] ) && ( $argument[0] === '$' || $argument[0] === '@' ) )
            {
                if ( $argument === '$serviceContainer' )
                {
                    // Self
                    $arguments[] = $this;
                }
                else if ( isset( $this->dependencies[ $argument ] ) )
                {
                    // Existing dependencies
                    $arguments[] = $this->dependencies[ $argument ];
                }
                else if ( $argument[0] === '$' )
                {
                    // Undefined variables will trow an exception
                    throw new InvalidArgumentValue( "arguments[{$key}]", $argument );
                }
                else
                {
                    // Try to load a @service dependency
                    $arguments[] = $this->get( ltrim( $argument, '@' ) );
                }
            }
            // Primitive type / object argument
            else
            {
                $arguments[] = $argument;
            }
        }

        // Use "new" if just 1 or 2 arguments (premature optimization to avoid use of reflection in most cases)
        if ( isset( $arguments[0] ) && !isset( $arguments[2] ) )
        {
            if ( !isset( $arguments[1] ) )
                return $this->dependencies[$serviceKey] = new $settings['class']( $arguments[0] );
            return $this->dependencies[$serviceKey] = new $settings['class']( $arguments[0], $arguments[1] );
        }

        // use Reflection to create a new instance, using the $args
        $reflectionObj = new ReflectionClass( $settings['class'] );
        return $this->dependencies[$serviceKey] = $reflectionObj->newInstanceArgs( $arguments );
    }
}
