<?php
/**
 * File contains Collection interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */

namespace ezp\base\Interfaces;

/**
 * Collection interface
 *
 * Note: Does not extend IteratorAggregate / Iterator to let implementers extend ArrayObject or splFixedArray
 *
 * @package ezp
 * @subpackage base
 */
interface Collection extends \Countable, \ArrayAccess, \Serializable
{
}
