<?php
/**
 * Abstract Content Field (content attribute) model object, used for content field and content type field
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

/**
 * Abstract field class
 */
namespace ezx\doctrine\model;
abstract class Abstract_Field implements Interface_Serializable
{
    /**
     * Abstract __constructor
     * Setups field value
     */
    public function __construct()
    {
    }

    /**
     * @throws \InvalidArgumentException
     * @param string $name
     * @return mixed
     */
    public function __get( $name )
    {
        if ( $name === 'value' )
            return $this->getValueObject()->value;
        elseif ( isset( $this->$name ) )
            return $this->$name;
        throw new \InvalidArgumentException( "{$name} is not a valid property on " . get_class($this) );
    }


    /**
     * @throws \InvalidArgumentException
     * @param string $name
     * @param mixed $value
     */
    public function __set( $name, $value )
    {
        switch ( $name )
        {
            case 'value':
                $this->getValueObject()->value = $value;
                break;
            default:
                if ( isset( $this->$name ) )
                    throw new \InvalidArgumentException( "{$name} is not a writable property on " . get_class($this) );
                else
                    throw new \InvalidArgumentException( "{$name} is not a valid property on " . get_class($this) );
        }
    }

    /**
     * Used by var_export and other functions to init class with all values
     *
     * @static
     * @param array $properties
     * @return Abstract_Field
     */
    public static function __set_state( array $properties )
    {
        $class = new static();
        return $class->setState( $properties );
    }

    /**
     * @throws \InvalidArgumentException
     * @param array $properties
     * @return Abstract_Field Return $this
     */
    public function setState( array $properties )
    {
        foreach ( $properties as $property => $value )
        {
            if ( $property === 'value' )
            {
                if ( $value instanceof Interface_Field_Value )
                    $this->getValueObject()->value = $value->value;
                else
                    $this->getValueObject()->value = $value;
            }
            else if ( $property === 'valueObject' && $value instanceof Interface_Field_Value )
            {
                $this->assignValue( $value );
            }
            else if ( isset( $this->$property ) )
            {
                $this->$property = $value;
            }
            else
                 throw new \InvalidArgumentException( "{$property} is not a valid property on " . __CLASS__ );
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getState()
    {
        $hash = array();
        foreach( $this as $property => $value )
        {
            if ( $value instanceof Interface_Field_Value )
                $hash[$property] = $value->value;
            else
                $hash[$property] = $value;
        }
        return $hash;
    }

    /**
     * @var Interface_Field_Value
     */
    private $valueObject;

    /**
     * Initialize and return field value
     *
     * @todo generalize code and remove knowledge of Field / ContentTypeField classes
     * @throws \RuntimeException If definition of Interface_Field_Value is wrong
     * @return Interface_Field_Value
     */
    protected function getValueObject()
    {
        if ( $this->valueObject instanceof Interface_Field_Value )
           return $this->valueObject;

        $configuration = \ezp\system\Configuration::getInstance();
        $list = $configuration->get( 'doctrine-fields', ( $this instanceof Field ? 'content' : 'type' ) );

        if ( !isset( $list[ $this->fieldTypeString ] ) )
            throw new \RuntimeException( "Field type value '{$this->fieldTypeString}' is not configured in system.ini" );

        if ( !class_exists( $list[ $this->fieldTypeString ] ) )
            throw new \RuntimeException( "Field type value class '{$list[$this->fieldTypeString]}' does not exist" );

        if ( !is_subclass_of( $list[ $this->fieldTypeString ], '\ezx\doctrine\model\Interface_Field_Value' ) )
            throw new \RuntimeException( "Field type value '{$list[$this->fieldTypeString]}' does not implement Interface_Field_Value" );

        $className = $list[ $this->fieldTypeString ];
        return $this->assignValue( new $className() );
    }

    /**
     * Assign value by reference to native property
     *
     * @throws \RuntimeException If definition of Interface_Field_Value is wrong
     * @param Interface_Field_Value $value
     * @return Interface_Field_Value
     */
    protected function assignValue( Interface_Field_Value $value )
    {
        $definition = $value::definition();
        $property = $definition['legacy_column'];

        if ( $this instanceof Field )
        {
            if ( $property !== 'data_int' && $property !== 'data_float' && $property !== 'data_text' )
                throw new \RuntimeException( "Definition from '$className' specifies non existing legacy_column: '$property'" );
        }
        else
        {
            if ( $property !== 'data_int1' && $property !== 'data_text1' )
                throw new \RuntimeException( "Definition from '$className' specifies non existing legacy_column: '$property'" );
        }

        $value->assignValue( $this->$property );
        return $this->valueObject = $value;
    }
}
