<?php
/**
 * File contains Abstract Content Field decorator (datatype) class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

/**
 *
 */
namespace ezp\Content;
abstract class AbstractFieldType extends \ezp\Base\AbstractModel implements \ezp\Base\Interfaces\Observer
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
     * @param \ezp\Base\Interfaces\Observable $subject
     * @param string $event
     * @return Field
     */
    public function update( \ezp\Base\Interfaces\Observable $subject, $event = 'update' )
    {
        if ( $subject instanceof AbstractField )
        {
            return $this->notify( $event );
        }
        return $this;
    }
}
