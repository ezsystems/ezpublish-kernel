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
namespace ezx\doctrine\content;
abstract class Abstract_Field extends Abstract_ContentModel implements \ezx\doctrine\Interface_Observer
{
    /**
     * @uses Abstract_Model::__get() If $name is something else then 'value'
     * @param string $name
     * @return mixed
     */
    public function __get( $name )
    {
        if ( $name === 'value' )
            return $this->getValueObject();
        return parent::__get( $name );
    }

    /**
     * @uses Abstract_Model::__set() If $name is something else then 'value'
     * @param string $name
     * @return mixed
     */
    public function __set( $name, $value )
    {
        if ( $name === 'value' )
            return $this->getValueObject()->setValue( $value );
        return parent::__set( $name, $value );
    }

    /**
     * @var Abstract_FieldValue
     */
    private $value;

    /**
     * Initialize and return field value
     *
     * @todo generalize code and remove knowledge of Field / ContentTypeField classes
     * @throws \RuntimeException If definition of Interface_Value is wrong
     * @return Abstract_FieldValue
     */
    public function getValueObject()
    {
        if ( $this->value instanceof \ezx\doctrine\Interface_Value )
           return $this->value;

        $configuration = \ezp\system\Configuration::getInstance();
        $list = $configuration->get( 'doctrine-fields', ( $this instanceof Field ? 'content' : 'type' ) );

        if ( !isset( $list[ $this->fieldTypeString ] ) )
            throw new \RuntimeException( "Field type value '{$this->fieldTypeString}' is not configured in system.ini" );

        if ( !class_exists( $list[ $this->fieldTypeString ] ) )
            throw new \RuntimeException( "Field type value class '{$list[$this->fieldTypeString]}' does not exist" );

        if ( !is_subclass_of( $list[ $this->fieldTypeString ], '\ezx\doctrine\Interface_Value' ) )
            throw new \RuntimeException( "Field type value '{$list[$this->fieldTypeString]}' does not implement Interface_Value" );

        $className = $list[ $this->fieldTypeString ];
        return $this->assignValue( new $className() );
    }

    /**
     * Assign value by reference to native property
     *
     * @throws \RuntimeException If definition of Interface_Value is wrong
     * @param \ezx\doctrine\Interface_Value $value
     * @return \ezx\doctrine\Interface_Value
     */
    protected function assignValue( \ezx\doctrine\Interface_Value $value )
    {
        $definition = $value::definition();
        $property = $definition['value']['legacy_column'];

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
     * @param Abstract_FieldValue $subject
     * @param string|null $event
     * @return Abstract_Field
     */
    public function update( \ezx\doctrine\Interface_Observable $subject , $event  = null )
    {
        if ( !$subject instanceof Abstract_FieldValue )
            return $this;

        $definition = $subject::definition();
        $property = $definition['value']['legacy_column'];

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
        return $this;
    }
}
