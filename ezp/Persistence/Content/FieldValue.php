<?php
/**
 * File containing the (content) FieldValue class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content;

/**
 * @package ezp
 * @subpackage persistence_content
 */
class FieldValue extends \ezp\Persistence\AbstractValueObject
{
    /**
     * Integer data.
     * 
     * @var int|null
     */
    public $intData;

    /**
     * Floating point data.
     * 
     * @var float|null
     */
    public $floatData;

    /**
     * Textual data.
     * 
     * @var string|null
     */
    public $textData;

    /**
     * Arbitrary external data.
     * 
     * @var mixed|null
     */
    public $externalData;

    /**
     * Integer key.
     * 
     * @var int|null
     */
    public $intKey;

    /**
     * String key.
     * 
     * @var string|null
     */
    public $stringKey;
}
