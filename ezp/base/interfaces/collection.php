<?php
/**
 * File contains Collection interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */

/**
 * Collection interface
 *
 * @todo Make sure interface is usable for both normal collections as well as lazy loaded collections
 */
namespace ezp\base;
interface CollectionInterface extends \Countable, \IteratorAggregate, \ArrayAccess
{
}
