<?php
/**
 * File containing the ezp\Content\Criteria\Operator class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage content/criteria
 */
namespace ezp\Content\Criteria;

/**
 * Operators struct
 */
class Operator
{
    const EQUALS = "=";
    const NOT_EQUALS = "!=";
    const LIKE = "like";
    const GREATER_THAN = ">";
    const GREATER_THAN_EQUALS = ">=";
    const LOWER_THAN = "<";
    const LOWER_THAN_EQUALS = "<=";
    const BETWEEN = "between";
    const NOT_BETWEEN = "not_between";
    const IN = "in";
    const NOT_IN = "not_in";
}
?>
