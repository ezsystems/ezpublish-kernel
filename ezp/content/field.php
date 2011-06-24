<?php
/**
 * File containing the ezp\content\Field class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content
 */

/**
 * This class represents a Content's field
 *
 * @package ezp
 * @subpackage content
 */
namespace ezp\content;
class Field extends \ezp\base\AbstractModel
{
    protected $fieldIdentifier;

    /**
     * Value Object (struct) for field
     * @var unknown_type
     */
    protected $value;
}
?>