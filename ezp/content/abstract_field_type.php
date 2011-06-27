<?php
/**
 * File contains Abstract Content Field decorator (datatype) class
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage content
 */

/**
 *
 */
namespace ezp\content;
abstract class AbstractFieldType extends \ezp\base\AbstractModel implements \ezp\base\ObserverInterface
{
    /**
     * Constant that Field types needs to defined
     * eg. ezstring
     * @var string
     */
    const FIELD_IDENTIFIER = '';

    /**
     * List of field type identifiers for use by design overrides
     * eg. ezstring
     * @var array
     */
    protected $types = array();

    /**
     * Constructor, appends $types
     */
    public function __construct()
    {
        //$this->types[] = self::FIELD_IDENTIFIER;
        //parent::__construct();
    }

    /**
     * Return list of identifiers for field type for design override use
     *
     * @return array
     */
    public function typeInheritance()
    {
        return $this->types;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '' . $this->value;
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
        if ( $subject instanceof Field )
        {
            return $this->notify( $event );
        }
        return $this;
    }
}
