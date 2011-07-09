<?php
/**
 * Service Container class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage base
 */

/**
 * Service container class
 * A dependency injection container aka a variant of
 * Registry pattern that reads configuration from settings
 * instead of requiring code to set it up.
 */
namespace ezp\Base;
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
     * @param array $callChainDependancieOverrides Overrides dependencies throughout the call (dependency) chain
     * @return \ezp\Base\Interfaces\Event
     */
    public function getEvent( array $callChainDependancieOverrides = array() )
    {
        if ( isset( $this->dependencies['@event'] ) )
            return $this->dependencies['@event'];
        return $this->get( 'event', $callChainDependancieOverrides );
    }

    /**
     * Service function to get Repository object
     *
     * @param array $callChainDependancieOverrides Overrides dependencies throughout the call (dependency) chain
     * @return \ezp\Base\Repository
     */
    public function getRepository( array $callChainDependancieOverrides = array() )
    {
        if ( isset( $this->dependencies['@repository'] ) )
            return $this->dependencies['@repository'];
        return $this->get( 'repository', $callChainDependancieOverrides );
    }

    /**
     * Get service by name
     *
     * @throws \InvalidArgumentException
     * @param string $serviceName
     * @param array $callChainDependancieOverrides Overrides dependencies throughout the call (dependency) chain
     * @return object
     */
    public function get( $serviceName, array $callChainDependancieOverrides = array() )
    {
        $serviceKey = "@{$serviceName}";
        if ( isset( $this->dependencies[$serviceKey] ) )
        {
            return $this->dependencies[$serviceKey];
        }

        if ( isset( $this->configurationOverrides[$serviceName] ) )
        {
            $settings = $this->configurationOverrides[$serviceName];
        }
        else
        {
            $settings = Configuration::getInstance()->getSection( "service_{$serviceName}", false );
        }

        // validate settings
        if ( $settings === false )
        {
            throw new \InvalidArgumentException( "{$serviceName} is not a valid Service(Configuration section service_{$serviceName} does not exist), ". __CLASS__ );
        }
        if ( empty( $settings['class'] ) )
        {
            throw new \InvalidArgumentException( "{$serviceName} does not have a Service class(value empty/ not defined), " . __CLASS__ );
        }
        if ( !class_exists( $settings['class'] ) )
        {
            throw new \InvalidArgumentException( "{$serviceName} does not have a valid Service class({$settings['class']} is not a valid class), " . __CLASS__ );
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
            if ( isset( $argument[0] ) && ( $argument[0] === '$' || $argument[0] === '@' ) )// service name / variable
            {
                if ( $argument === '$serviceContainer' )
                    $arguments[] = $this;
                else if ( isset( $callChainDependancieOverrides[ $argument ] ) )
                    $arguments[] = $callChainDependancieOverrides[ $argument ];
                else if ( isset( $this->dependencies[ $argument ] ) )
                    $arguments[] = $this->dependencies[ $argument ];
                else if ( $argument[0] === '$' )
                    throw new \InvalidArgumentException( "$serviceName argument $key => $argument is not a valid variable, ". __CLASS__ );
                else
                    goto loadDependency;
            }
            else // primitive type / object argument
            {
                $arguments[] = $argument;
            }
            continue;

            loadDependency: { // load service dependency
                try
                {
                    $arguments[] = $this->get( ltrim( $argument, '@' ), $callChainDependancieOverrides );
                }
                catch ( \InvalidArgumentException $e )
                {
                    throw new \InvalidArgumentException( "$serviceName argument {$settings['arguments'][$key]} => $argument threw an exception, ". __CLASS__, 0, $e );
                }
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
        $reflectionObj = new \ReflectionClass( $settings['class'] );
        return $this->dependencies[$serviceKey] = $reflectionObj->newInstanceArgs( $arguments );
    }
}
