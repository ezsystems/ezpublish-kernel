<?php
/**
 * Abstract Field (content [class] attribute) model object, used for content field and content type field
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */

/**
 * Abstract field class
 */
namespace ezx\content;
abstract class Abstract_Field extends Abstract_ContentModel implements \ezx\base\Interface_Observer
{
    /**
     * @var Abstract_FieldType
     */
    protected $type;

    /**
     * Initialize and return field type
     *
     * @throws \RuntimeException If definition of Abstract_FieldType is wrong
     * @return Abstract_FieldType
     */
    public function getType()
    {
        if ( $this->type instanceof Abstract_FieldType )
           return $this->type;

        $configuration = \ezp\base\Configuration::getInstance('content');
        $list = $configuration->get( 'field-types', ( $this instanceof ContentField ? 'content' : 'contentType' ) );

        if ( !isset( $list[ $this->fieldTypeString ] ) )
            throw new \RuntimeException( "Field type value '{$this->fieldTypeString}' is not configured in system.ini" );

        if ( !class_exists( $list[ $this->fieldTypeString ] ) )
            throw new \RuntimeException( "Field type value class '{$list[$this->fieldTypeString]}' does not exist" );

        $className = $list[ $this->fieldTypeString ];
        $this->type = $this->initType( $className );

        return $this->type->attach( $this );
    }

    /**
     * Called when subject has been updated
     *
     * @param \ezx\base\Interface_Observable $subject
     * @param string|null $event
     * @return Abstract_Field
     */
    public function update( \ezx\base\Interface_Observable $subject , $event  = null )
    {
        if ( !$subject instanceof Abstract_FieldType )
            return $this;

        $type = $this->getType();
        if ( $type !== $subject )
            throw new \RuntimeException( "Field should only listen to it's own attached field value, not others! type: '{$this->fieldTypeString}' " );

        return $this->fromType( $type );
    }

    /**
     * Initialize field type class
     *
     * @throws \RuntimeException If $className is not instanceof Abstract_FieldType
     * @param string $className
     * @return Abstract_FieldType
     */
    protected function initType( $className )
    {
        $type = new $className();
        if ( !$type instanceof Abstract_FieldType )
            throw new \RuntimeException( "Field type value '{$className}' does not implement Abstract_FieldType" );
        $this->toType( $type );
        return $type;
    }

    /**
     * Set values from field type to field
     *
     * @param Abstract_FieldType $type
     * @return Abstract_Field
     */
    protected function fromType( Abstract_FieldType $type )
    {
        foreach ( $type->definition() as $property => $propertyDefinition )
        {
            $legacyProperty = $propertyDefinition['legacy_column'];
            if ( isset( $this->$legacyProperty ) )
                $this->$legacyProperty = $type->$property;
            else
                throw new \RuntimeException( "'{$legacyProperty}' is not a valid property on " . get_class( $this ) );
        }
        return $this;
    }

    /**
     * Set values from field type to field
     *
     * @param Abstract_FieldType $type
     * @return Abstract_Field
     */
    protected function toType( Abstract_FieldType $type )
    {
        foreach ( $type->definition() as $property => $propertyDefinition )
        {
            $legacyProperty = $propertyDefinition['legacy_column'];
            if ( isset( $this->$legacyProperty ) )
                $type->$property = $this->$legacyProperty;
            else
                throw new \RuntimeException( "'{$legacyProperty}' is not a valid property on " . get_class( $this ) );
        }
        return $this;
    }
}
