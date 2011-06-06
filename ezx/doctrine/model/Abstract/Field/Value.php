<?php
/**
 * Abstract Content Field decorator (datatype) object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

/**
 *
 */
namespace ezx\doctrine\model;
abstract class Abstract_Field_Value extends Abstract_ContentModel implements Interface_Value
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
     * The value of this value object
     * @var mixed
     */
    protected $value;

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
     * Set value
     *
     * @param mixed $value As defined by defintion()
     * @return Abstract_Field_Value
     */
    public function setValue( $value )
    {
        $this->value = $value;
        $this->notify();
        return $this;
    }

    /**
     * Get value
     *
     * @return mixed As defined by defintion()
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '' . $this->value;
    }
}
