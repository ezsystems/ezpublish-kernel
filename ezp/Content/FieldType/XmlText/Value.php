<?php
/**
 * File containing the XmlText Value class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\XmlText;
use ezp\Content\FieldType\ValueInterface,
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Persistence\Content\FieldValue as PersistenceFieldValue;

/**
 * Basic, raw value for TextLine field type
 */
class Value extends BaseValue implements ValueInterface
{
    /**
     * Text content
     *
     * @var string
     */
    public $text;

    /**
     * Construct a new Value object and initialize it to $text
     *
     * @param string $text
     */
    public function __construct( $text = '' )
    {
        $this->text = $text;
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->text;
    }

    /**
     * @see \ezp\Content\FieldType\ValueInterface::getTitle()
     */
    public function getTitle()
    {
        throw new \RuntimeException( 'Implement this method' );
    }
}
