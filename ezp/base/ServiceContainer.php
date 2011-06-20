<?php
/**
 * Service Container class
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */

/**
 * Service container class
 * A dependency injection container aka a variant of
 * Registry pattern that reads configuration from settings
 * instead of requiring code to set it up.
 */
namespace ezp\base;
class ServiceContainer
{
    /**
     * Holds service objects and variables
     *
     * @var array(string=>object)
     */
    private $dependancies = array();

    /**
     * Instance overrides for configuration
     *
     * @var array(string=>array)
     */
    private $configurationOverrides = array();

    /**
     * Construct object with optiona configuration overrides
     *
     * @param array(string=>array) $configurationOverrides
     * @param array(string=>object) $dependancies
     */
    public function __construct( array $configurationOverrides = array(),
                                 array $dependancies = array(
                                    '$_SERVER' => $_SERVER,
                                    '$_REQUEST' => $_REQUEST,
                                    '$_COOKIE' => $_COOKIE,
                                    '$_FILES' => $_FILES
                                 ) )
    {
        $this->configurationOverrides = $configurationOverrides;
        $this->dependancies = $dependancies;
    }

    /**
     * Service function to get Event instance.
     *
     * @param array $callChainDependancieOverrides Overrides dependacies troughout the call (dependancie) chain
     * @return ezp\base\interfaces\Event
     */
    public function getEvent( array $callChainDependancieOverrides = array() )
    {
        if ( isset( $this->dependancies['@event'] ) )
            return $this->dependancies['@event'];
        return $this->get( 'event', $callChainDependancieOverrides );
    }

    /**
     * Service function to get Response object
     *
     * @param array $callChainDependancieOverrides Overrides dependacies troughout the call (dependancie) chain
     * @return ezp\base\interfaces\Response
     */
    public function getResponse( array $callChainDependancieOverrides = array() )
    {
        if ( isset( $this->dependancies['@response'] ) )
            return $this->dependancies['@response'];
        return $this->get( 'response', $callChainDependancieOverrides );
    }

    /**
     * Service function to get Repository object
     *
     * @param array $callChainDependancieOverrides Overrides dependacies troughout the call (dependancie) chain
     * @return ezp\base\interfaces\Repository
     */
    public function getRepository( array $callChainDependancieOverrides = array() )
    {
        if ( isset( $this->dependancies['@repository'] ) )
            return $this->dependancies['@repository'];
        return $this->get( 'repository', $callChainDependancieOverrides );
    }

    /**
     * Service function to get StorageEngine object
     *
     * @param array $callChainDependancieOverrides Overrides dependacies troughout the call (dependancie) chain
     * @return ezp\base\interfaces\StorageEngine
     */
    public function getStorageEngine( array $callChainDependancieOverrides = array() )
    {
        if ( isset( $this->dependancies['@storage_engine'] ) )
            return $this->dependancies['@storage_engine'];
        return $this->get( 'storage_engine', $callChainDependancieOverrides );
    }

    /**
     * Get service by name
     *
     * @throws \InvalidArgumentException
     * @param string $serviceName
     * @param array $callChainDependancieOverrides Overrides dependacies troughout the call (dependancy) chain
     * @return object
     */
    public function get( $serviceName, array $callChainDependancieOverrides = array() )
    {
        $serviceKey = "@{$serviceName}";
        if ( isset( $this->dependancies[$serviceKey] ) )
        {
            return $this->dependancies[$serviceKey];
        }

        if ( isset( $this->configurationOverrides[$serviceName] ) )
        {
            $settings = $this->configurationOverrides[$serviceName];
        }
        else
        {
            $settings = Configuration::getInstance('system')->getSection( "service_{$serviceName}", false );
        }

        // validate settings
        if ( $settings === false )
        {
            throw new \InvalidArgumentException( "{$serviceName} is not a valid Service(Configuration section service_{$serviceName} does not exist), ". __CLASS__ );
        }
        else if ( empty( $settings['class'] ) )
        {
            throw new \InvalidArgumentException( "{$serviceName} does not have a Service class(value empty/ not defined), " . __CLASS__ );
        }
        else if ( !class_exists( $settings['class'] ) )
        {
            throw new \InvalidArgumentException( "{$serviceName} does not have a valid Service class({$settings['class']} is not a valid class), " . __CLASS__ );
        }

        // Create service directly if it does not have any arguments
        if ( empty( $settings['arguments'] ) )
        {
            return $this->dependancies[$serviceKey] = new $settings['class']();
        }

        // Expand arguments with other service objects on arguments that start with @ and predefined variables that start with $
        $arguments = array();
        foreach( $settings['arguments'] as $key => $argument )
        {
            if ( isset( $argument[0] ) && ( $argument[0] === '$' || $argument[0] === '@' ) )// service name / variable
            {
                if ( $argument === '$serviceContainer' )
                    $arguments[] = $this;
                else if ( isset( $callChainDependancieOverrides[ $argument ] ) )
                    $arguments[] = $callChainDependancieOverrides[ $argument ];
                else if ( isset( $this->dependancies[ $argument ] ) )
                    $arguments[] = $this->dependancies[ $argument ];
                else if ( $argument[0] === '$' )
                    throw new \InvalidArgumentException( "$serviceName argument $key => $argument is not a valid variable, ". __CLASS__ );
                else
                    goto loadDependancy;
            }
            else // primitive type / object argument
            {
                $arguments[] = $argument;
            }
            continue;

            loadDependancy: { // load service dependancy
                try
                {
                    $arguments[] = $this->get( ltrim( $argument, '@' ), $callChainDependancieOverrides );
                }
                catch( \InvalidArgumentException $e )
                {
                    throw new \InvalidArgumentException( "$serviceName argument {$settings['arguments'][$key]} => $argument threw an exception, ". __CLASS__, 0, $e );
                }
            }
        }

        // Use "new" if just 1 or 2 arguments (premature optimization to avoid use of reflection in most cases)
        if ( isset( $arguments[0] ) && !isset( $arguments[2] ) )
        {
            if ( !isset( $arguments[1] ) )
                return $this->dependancies[$serviceKey] = new $settings['class']( $arguments[0] );
            return $this->dependancies[$serviceKey] = new $settings['class']( $arguments[0], $arguments[1] );
        }

        // use Reflection to create a new instance, using the $args
        $reflectionObj = new \ReflectionClass( $settings['class'] );
        return $this->dependancies[$serviceKey] = $reflectionObj->newInstanceArgs( $arguments );
    }
}
