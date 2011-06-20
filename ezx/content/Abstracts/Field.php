<?php
/**
 * Abstract Field (content [class] attribute) domain object, used for content field and content type field
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */

/**
 * Abstract field class
 */
namespace ezx\content\Abstracts;
abstract class Field extends ContentModel implements \ezx\base\Interfaces\Observer
{
    /**
     * @var FieldType
     */
    protected $type;

    /**
     * Initialize and return field type
     *
     * @throws \RuntimeException If definition of _FieldType is wrong
     * @return FieldType
     */
    public function getType()
    {
        if ( $this->type instanceof FieldType )
           return $this->type;

        $configuration = \ezp\base\Configuration::getInstance('content');
        //@todo Remove hardcoded knowledge of sub class
        $list = $configuration->get( 'field-types', ( $this instanceof \ezx\content\ContentField ? 'content' : 'contentType' ) );

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
     * @param \ezx\base\Interfaces\Observable $subject
     * @param string|null $event
     * @return Field
     */
    public function update( \ezx\base\Interfaces\Observable $subject , $event  = null )
    {
        if ( !$subject instanceof FieldType )
            return $this;

        $type = $this->getType();
        if ( $type !== $subject )
            throw new \RuntimeException( "Field should only listen to it's own attached field value, not others! type: '{$this->fieldTypeString}' " );

        return $this->fromType( $type );
    }

    /**
     * Initialize field type class
     *
     * @throws \RuntimeException If $className is not instanceof FieldType
     * @param string $className
     * @return FieldType
     */
    protected function initType( $className )
    {
        $type = new $className();
        if ( !$type instanceof FieldType )
            throw new \RuntimeException( "Field type value '{$className}' does not implement ezx\\content\\Abstracts\\FieldType" );
        $this->toType( $type );
        return $type;
    }

    /**
     * Set values from field type to field
     *
     * @param FieldType $type
     * @return Field
     */
    protected function fromType( FieldType $type )
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
     * @param FieldType $type
     * @return Field
     */
    protected function toType( FieldType $type )
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
