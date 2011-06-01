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
abstract class Abstract_Field extends Abstract_Model implements Interface_Observer
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
            return $this->getValueObject()->getValue();
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
                $this->getValueObject()->setValue( $value );
                break;
            default:
                if ( isset( $this->$name ) )
                    throw new \InvalidArgumentException( "{$name} is not a writable property on " . get_class($this) );
                else
                    throw new \InvalidArgumentException( "{$name} is not a valid property on " . get_class($this) );
        }
    }

    /**
     * @var Interface_Field_Value
     */
    protected $value;

    /**
     * Initialize and return field value
     *
     * @todo generalize code and remove knowledge of Field / ContentTypeField classes
     * @throws \RuntimeException If definition of Interface_Field_Value is wrong
     * @return Abstract_Field_Value
     */
    protected function getValueObject()
    {
        if ( $this->value instanceof Interface_Field_Value )
           return $this->value;

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
     * Get properties with hash, name is same as used in ezc Persistent
     *
     * @return array
     */
    public function getState()
    {
        $hash = array();
        foreach( $this as $property => $value )
        {
            if ( $property[0] === '_' )
                continue;

            if ( $value instanceof Interface_Field_Value )
                $hash[$property] = $value->getValue();
            else if ( $value instanceof Interface_Serializable )
                $hash[$property] = $value->getState();
            else
                $hash[$property] = $value;
        }
        return $hash;
    }

    /**
     * Set properties with hash, name is same as used in ezc Persistent
     *
     * @param array $properties
     * @return Abstract_Model Content Return $this
     */
    public function setState( array $properties )
    {
        foreach ( $properties as $property => $value )
        {
            if ( $property === 'value' )
                $this->getValueObject()->setValue( $value );
            else if ( $this->$property instanceof Interface_Serializable && !$value instanceof Interface_Serializable )
                $this->$property->setState( $value );
            else
                $this->$property = $value;
        }
        return $this;
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

        return $this->value = $value->setValue( $this->$property )->attach( $this );
    }

    /**
     * Called when subject has been updated
     *
     * @param Abstract_Field_Value $subject
     * @param string|null $event
     * @return Abstract_Field
     */
    public function update( Interface_Observable $subject , $event  = null )
    {
        $definition = $subject::definition();
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

        $this->$property = $subject->getValue();
    }
}
