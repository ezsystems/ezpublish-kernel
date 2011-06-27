<?php
/**
 * Contains Abstract Field (content [class] attribute) class
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage content
 */

/**
 * Abstract field class, used for content field and content type field
 */
namespace ezp\content;
abstract class AbstractField extends \ezp\base\AbstractModel implements \ezp\base\ObserverInterface
{
    /**
     * @var AbstractFieldType
     */
    protected $type;

    /**
     * Initialize and return field type
     *
     * @throws \RuntimeException If definition of AbstractFieldType is wrong
     * @return AbstractFieldType
     */
    protected function getType()
    {
        if ( $this->type instanceof AbstractFieldType )
           return $this->type;

        $configuration = \ezp\base\Configuration::getInstance('content');
        //@todo Remove hardcoded knowledge of sub class
        $list = $configuration->get( 'fields', ( $this instanceof \ezp\content\Field ? 'Type' : 'Definition' ) );

        if ( !isset( $list[ $this->fieldTypeString ] ) )
            throw new \RuntimeException( "Field type value '{$this->fieldTypeString}' is not configured in system.ini" );

        if ( !class_exists( $list[ $this->fieldTypeString ] ) )
            throw new \RuntimeException( "Field type value class '{$list[$this->fieldTypeString]}' does not exist" );

        $className = $list[ $this->fieldTypeString ];
        $this->type = $this->initType( $className );

        return $this->attach( $this->type, 'store' )->type->attach( $this, 'store' );// listen on each other and return type
    }

    /**
     * Initialize field type class
     *
     * @throws \RuntimeException If $className is not instanceof AbstractFieldType
     * @param string $className
     * @return AbstractFieldType
     */
    protected function initType( $className )
    {
        $type = new $className();
        if ( !$type instanceof AbstractFieldType )
            throw new \RuntimeException( "Field type value '{$className}' does not implement ezp\\content\\AbstractFieldType" );
        $this->toType( $type );
        return $type;
    }

    /**
     * Set values from field type to field
     *
     * @param AbstractFieldType $type
     * @return Field
     */
    protected function fromType( AbstractFieldType $type )
    {
        foreach ( $type->properties() as $property => $legacyProperty )
        {
            if ( isset( $this->readableProperties[$legacyProperty] ) )
                $this->$legacyProperty = $type->$property;
            else
                throw new \RuntimeException( "'{$legacyProperty}' is not a valid property on " . get_class( $this ) );
        }
        return $this;
    }

    /**
     * Set values from field type to field
     *
     * @param AbstractFieldType $type
     * @return Field
     */
    protected function toType( AbstractFieldType $type )
    {
        foreach ( $type->properties() as $property => $legacyProperty )
        {
            if ( isset( $this->readableProperties[$legacyProperty] ) )
                $type->$property = $this->$legacyProperty;
            else
                throw new \RuntimeException( "'{$legacyProperty}' is not a valid property on " . get_class( $this ) );
        }
        return $this;
    }

    /**
     * Called when subject has been updated
     *
     * @param \ezp\base\ObservableInterface $subject
     * @param string $event
     * @return Field
     */
    public function update( \ezp\base\ObservableInterface $subject, $event = 'update' )
    {
        if ( !$subject instanceof AbstractFieldType )
            return $this;

        $type = $this->getType();
        if ( $type !== $subject )
            throw new \RuntimeException( "Field should only listen to it's own attached field value, not others! type: '{$this->fieldTypeString}' " );

        return $this->fromType( $type );
    }
}
