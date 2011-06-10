<?php
/**
 * File containing Criteria class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage content/criteria
 */
namespace ezp\Content\Criteria;

/**
 * Base class for criterias
 */
abstract class Criteria
{
    const OP_EQUALS = "=";
    const OP_NOT_EQUALS = "!=";
    const OP_LIKE = "like";
    const OP_GREATER_THAN = ">";
    const OP_GREATER_THAN_EQUALS = ">=";
    const OP_LOWER_THAN = "<";
    const OP_LOWER_THAN_EQUALS = "<=";
    const OP_BETWEEN = "between";
    const OP_NOT_BETWEEN = "not_between";
    const OP_IN = "in";
    const OP_NOT_IN = "not_in";
}
?>
