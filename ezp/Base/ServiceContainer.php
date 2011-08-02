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
 * A dependency injection container aka a variant of
 * Registry pattern that reads configuration from settings
 * instead of requiring code to set it up.
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
     * Instance overrides for configuration
     *
     * @var array[]
     */
    private $configurationOverrides;

    /**
     * Construct object with optional configuration overrides
     *
     * @param array[] $configurationOverrides
     * @param array[]|object[] $dependencies
     */
    public function __construct( array $configurationOverrides = array(), array $dependencies = array() )
    {
        $this->configurationOverrides = $configurationOverrides;
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
        if ( isset( $this->configurationOverrides[$serviceName] ) )
        {
            $settings = $this->configurationOverrides[$serviceName];
        }
        else
        {
            $settings = Configuration::getInstance()->getSection( "service_{$serviceName}", false );
        }

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
            continue;
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
