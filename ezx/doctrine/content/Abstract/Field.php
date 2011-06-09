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

        $configuration = \ezp\system\Configuration::getInstance();
        $list = $configuration->get( 'doctrine-fields', ( $this instanceof Field ? 'content' : 'type' ) );

        if ( !isset( $list[ $this->fieldTypeString ] ) )
            throw new \RuntimeException( "Field type value '{$this->fieldTypeString}' is not configured in system.ini" );

        if ( !class_exists( $list[ $this->fieldTypeString ] ) )
            throw new \RuntimeException( "Field type value class '{$list[$this->fieldTypeString]}' does not exist" );

        $className = $list[ $this->fieldTypeString ];
        $this->type = new $className();

        if ( !$this->type instanceof Abstract_FieldType )
            throw new \RuntimeException( "Field type value '{$list[$this->fieldTypeString]}' does not implement Abstract_FieldType" );

        foreach ( $this->type->definition() as $property => $propertyDefinition )
        {
            $legacyProperty = $propertyDefinition['legacy_column'];
            if ( isset( $this->$legacyProperty ) )
                $this->type->$property = $this->$legacyProperty;
            else
                throw new \RuntimeException( "'{$legacyProperty}' is not a valid property on " . get_class( $this ) );
        }

        return $this->type->attach( $this );
    }

    /**
     * Called when subject has been updated
     *
     * @param Abstract_FieldType $subject
     * @param string|null $event
     * @return Abstract_Field
     */
    public function update( \ezx\doctrine\Interface_Observable $subject , $event  = null )
    {
        if ( !$subject instanceof Abstract_FieldType )
            return $this;

        $type = $this->getType();
        if ( $type !== $subject )
            throw new \RuntimeException( "Field should only listen to it's own attached field value, not others! type: '{$this->fieldTypeString}' " );

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
}
